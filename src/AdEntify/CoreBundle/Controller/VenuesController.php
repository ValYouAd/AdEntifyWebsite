<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 18/04/2013
 * Time: 18:46
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Form\VenueType;
use AdEntify\CoreBundle\Util\CommonTools;
use Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection;

use AdEntify\CoreBundle\Entity\Venue;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\HttpException;

use AdEntify\CoreBundle\Entity\Tag;
use AdEntify\CoreBundle\Entity\Photo;
use AdEntify\CoreBundle\Util\UserCacheManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use AdEntify\CoreBundle\Util\PaginationTools;

/**
 * Class VenuesController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("Venue")
 */
class VenuesController extends FosRestController
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get a collection of all venues",
     *  output="AdEntify\CoreBundle\Entity\Venue",
     *  section="Venue"
     * )
     *
     * @View(serializerGroups={"list"})
     */
    public function cgetAction()
    {
        return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Venue')->findAll();
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get a Venue",
     *  output="AdEntify\CoreBundle\Entity\Venue",
     *  section="Venue"
     * )
     *
     * @View(serializerGroups={"details"})
     *
     * @return Venue
     */
    public function getAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $securityContext = $this->container->get('security.context');
        $user = null;
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->container->get('security.context')->getToken()->getUser();
        }

        // If no user connected, 0 is default
        $facebookFriendsIds = array(0);
        $followings = array(0);
        $followedBrands = array(0);

        if ($user) {
            // Get friends list (id) array
            $facebookFriendsIds = UserCacheManager::getInstance()->getUserObject($user, UserCacheManager::USER_CACHE_KEY_FB_FRIENDS);
            if (!$facebookFriendsIds) {
                $facebookFriendsIds = $em->getRepository('AdEntifyCoreBundle:User')->refreshFriends($user, $this->container->get('fos_facebook.api'));
                UserCacheManager::getInstance()->setUserObject($user, UserCacheManager::USER_CACHE_KEY_FB_FRIENDS, $facebookFriendsIds, UserCacheManager::USER_CACHE_TTL_FB_FRIENDS);
            }

            // Get followings ids
            $followings = UserCacheManager::getInstance()->getUserObject($user, UserCacheManager::USER_CACHE_KEY_FOLLOWINGS);
            if (!$followings) {
                $followings = $em->getRepository('AdEntifyCoreBundle:User')->getFollowingsIds($user, 0);
                UserCacheManager::getInstance()->setUserObject($user, UserCacheManager::USER_CACHE_KEY_FOLLOWINGS, $followings, UserCacheManager::USER_CACHE_TTL_FOLLOWING);
            }

            // Get following brands ids
            $followedBrands = UserCacheManager::getInstance()->getUserObject($user, UserCacheManager::USER_CACHE_KEY_BRAND_FOLLOWINGS);
            if (!$followedBrands) {
                $followedBrands = $em->getRepository('AdEntifyCoreBundle:User')->getFollowedBrandsIds($user);
                UserCacheManager::getInstance()->setUserObject($user, UserCacheManager::USER_CACHE_KEY_BRAND_FOLLOWINGS, $followedBrands, UserCacheManager::USER_CACHE_TTL_BRAND_FOLLOWINGS);
            }
        }

        $venue = $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Venue')->find($id);
        if ($venue) {
            $lastPhoto = $em->createQuery('SELECT photo
                                           FROM AdEntifyCoreBundle:Photo photo
                                           LEFT JOIN photo.tags tag INNER JOIN photo.owner owner LEFT JOIN tag.brand brand LEFT JOIN tag.venue venue
                                           WHERE venue.id = :venueId AND photo.status = :status AND photo.deletedAt IS NULL
                                              AND (photo.visibilityScope = :visibilityScope
                                                OR (owner.facebookId IS NOT NULL AND owner.facebookId IN (:facebookFriendsIds))
                                                OR owner.id IN (:followings)
                                                OR brand.id IN (:followedBrands))
                                           ORDER BY photo.id DESC')
                ->setParameters(array(
                    ':status' => Photo::STATUS_READY,
                    ':visibilityScope' => Photo::SCOPE_PUBLIC,
                    ':facebookFriendsIds' => $facebookFriendsIds,
                    ':followings' => $followings,
                    ':followedBrands' => $followedBrands,
                    ':venueId' => $venue->getId(),
                ))
                ->setMaxResults(1)
                ->getOneOrNullResult();

            $venue->setLastPhoto($lastPhoto);
            return $venue;
        } else
            throw new HttpException(404);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Post a Venue",
     *  input="AdEntify\CoreBundle\Form\VenueType",
     *  output="AdEntify\CoreBundle\Entity\Venue",
     *  section="Venue"
     * )
     *
     * @View(serializerGroups={"details"})
     */
    public function postAction(Request $request)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $em = $this->getDoctrine()->getManager();
            $venueRequest = $request->request->get('venue');

            if ($request->request->has('venue')) {
                // Check if existing foursquare id exist
                if (array_key_exists('foursquare_id', $venueRequest)) {
                    $venue = $em->getRepository('AdEntifyCoreBundle:Venue')->findOneBy(array(
                        'foursquareId' => $venueRequest['foursquare_id']
                    ));
                } else {
                    $venue = null;
                }

                // Update venue products
                if ($venue && is_array($venueRequest) && array_key_exists('products', $venueRequest) && count($venueRequest['products']) > 0) {
                    $this->addProductsToVenue($venue, $venueRequest, $em);
                    $em->merge($venue);
                    $em->flush();
                }

                if ($venue) {
                    return $venue;
                }
            }

            $venue = new Venue();
            $form = $this->getForm($venue);
            $form->bind($request);
            if ($form->isValid()) {
                if ($venue->getLink())
                    $venue->setLink(CommonTools::addScheme($venue->getLink()));

                // Add venue products
                $this->addProductsToVenue($venue, $venueRequest, $em);
                if ($venue->getLink()) {
                    $shortUrl = $em->getRepository('AdEntifyCoreBundle:ShortUrl')->createShortUrl($venue->getLink());
                    if ($shortUrl)
                        $venue->setShortUrl($shortUrl)->setLink($this->generateUrl('redirect_url', array(
                            'id' => $shortUrl->getBase62Id()
                        )));
                }
                $em->persist($venue);
                $em->flush();

                return $venue;
            } else {
                return $form;
            }
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @param $query
     * @param int $limit (max : 50)
     * @param null $ll latitude & longitude of current position
     * @param int $radius (in meters)
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Search a venue with a query (keyword)",
     *  output="AdEntify\CoreBundle\Entity\Venue",
     *  section="Venue"
     * )
     *
     * @QueryParam(name="limit", default="30")
     * @QueryParam(name="ll", default="", description="Latitude and longitude of the user's location. Example : 44.3,37.2. If it's not specified, globally search.")
     * @QueryParam(name="radius", default="10000", description="Limit results to venues within this many meters of the specified location. Maximum 100,000 meters")
     * @View(serializerGroups={"list"})
     */
    public function getSearchAction($query, $limit, $ll, $radius)
    {
        $url = 'https://api.foursquare.com/v2/venues/search';
        // If latitude/longitude given, search locally
        if (!empty($ll)) {
            $url .= '?intent=browse&radius=' . $radius . '&ll=' . $ll;
        } else {
            $url .= '?intent=global';
        }
        $url .= '&limit=' . $limit . '&query=' . urlencode($query) . '&client_id=' . $this->container->getParameter('foursquare.client_id')
            . '&client_secret=' . $this->container->getParameter('foursquare.client_secret') . '&v='.date('Ymd');

        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, 5);
        $response = curl_exec($ch);
        curl_close($ch);

        $venues = $this->getDoctrine()->getManager()->createQuery('SELECT v FROM AdEntifyCoreBundle:Venue v WHERE v.name LIKE :query')
            ->setMaxResults($limit)
            ->setParameters(array(
                'query' => '%'.$query.'%'
            ))
            ->getResult();

        if (!$venues)
            $venues = array();

        if ($response !== false) {
            $response = json_decode($response);
            if ($response->response->venues && count($response->response->venues) > 0) {
                foreach($response->response->venues as $val) {
                    $venue = new Venue();
                    $venue->setName($val->name)
                        ->setFoursquareId($val->id)
                        ->setLink(isset($val->url) ? $val->url : '')
                        ->setLat($val->location->lat)
                        ->setLng($val->location->lng);
                    if (isset($val->canonicalUrl))
                        $venue->setFoursquareShortLink($val->canonicalUrl);
                    if (isset($val->location->address))
                        $venue->setAddress($val->location->address);
                    if (isset($val->location->city))
                        $venue->setCity($val->location->city);
                    if (isset($val->location->postalCode))
                        $venue->setPostalCode($val->location->postalCode);
                    if (isset($val->location->state))
                        $venue->setState($val->location->state);
                    if (isset($val->location->cc))
                        $venue->setCc($val->location->cc);
                    if (isset($val->location->country))
                        $venue->setCountry($val->location->country);
                    $venues[] = $venue;
                }
            }
            return $venues;
        } else {
            throw new \Exception('Cannot search venues.');
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get a Venue's photos",
     *  output="AdEntify\CoreBundle\Entity\Photo",
     *  section="Venue"
     * )
     *
     * @View(serializerGroups={"list"})
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="30")
     * @QueryParam(name="orderBy", default="null")
     * @QueryParam(name="way", requirements="DESC|ASC", default="DESC")
     *
     */
    public function getPhotosAction($id, $page, $limit, $orderBy, $way)
    {
        $em = $this->getDoctrine()->getManager();

        $securityContext = $this->container->get('security.context');
        $user = null;
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->container->get('security.context')->getToken()->getUser();
        }

        // If no user connected, 0 is default
        $facebookFriendsIds = array(0);
        $followings = array(0);
        $followedBrands = array(0);

        if ($user) {
            // Get friends list (id) array
            $facebookFriendsIds = UserCacheManager::getInstance()->getUserObject($user, UserCacheManager::USER_CACHE_KEY_FB_FRIENDS);
            if (!$facebookFriendsIds) {
                $facebookFriendsIds = $em->getRepository('AdEntifyCoreBundle:User')->refreshFriends($user, $this->container->get('fos_facebook.api'));
                UserCacheManager::getInstance()->setUserObject($user, UserCacheManager::USER_CACHE_KEY_FB_FRIENDS, $facebookFriendsIds, UserCacheManager::USER_CACHE_TTL_FB_FRIENDS);
            }

            // Get followings ids
            $followings = UserCacheManager::getInstance()->getUserObject($user, UserCacheManager::USER_CACHE_KEY_FOLLOWINGS);
            if (!$followings) {
                $followings = $em->getRepository('AdEntifyCoreBundle:User')->getFollowingsIds($user, 0);
                UserCacheManager::getInstance()->setUserObject($user, UserCacheManager::USER_CACHE_KEY_FOLLOWINGS, $followings, UserCacheManager::USER_CACHE_TTL_FOLLOWING);
            }

            // Get following brands ids
            $followedBrands = UserCacheManager::getInstance()->getUserObject($user, UserCacheManager::USER_CACHE_KEY_BRAND_FOLLOWINGS);
            if (!$followedBrands) {
                $followedBrands = $em->getRepository('AdEntifyCoreBundle:User')->getFollowedBrandsIds($user);
                UserCacheManager::getInstance()->setUserObject($user, UserCacheManager::USER_CACHE_KEY_BRAND_FOLLOWINGS, $followedBrands, UserCacheManager::USER_CACHE_TTL_BRAND_FOLLOWINGS);
            }
        }

        $parameters = null;
        $countQuery = null;
        $dataQuery = null;
        $joinClause = null;

        $parameters = array(
            ':status' => Photo::STATUS_READY,
            ':visibilityScope' => Photo::SCOPE_PUBLIC,
            ':facebookFriendsIds' => $facebookFriendsIds,
            ':followings' => $followings,
            ':followedBrands' => $followedBrands,
            ':venue_id' => $id,
        );

        $sql = sprintf('SELECT photo
                        FROM AdEntify\CoreBundle\Entity\Photo photo
                        LEFT JOIN photo.tags tag
                        INNER JOIN photo.owner owner
                        LEFT JOIN tag.brand brand %s
                        WHERE tag.venue = :venue_id
                          AND photo.status = :status
                          AND photo.deletedAt IS NULL
                          AND (photo.visibilityScope = :visibilityScope
                            OR (owner.facebookId IS NOT NULL AND owner.facebookId IN (:facebookFriendsIds)) OR owner.id IN (:followings) OR brand.id IN (:followedBrands))', $joinClause);

        if ($orderBy) {
            switch ($orderBy) {
                case 'likes':
                    $sql .= sprintf(' ORDER BY photo.likesCount %s', $way);
                    break;
                case 'points':
                    $sql .= sprintf(' ORDER BY photo.totalTagsPoints %s', $way);
                    break;
                case 'date':
                default:
                    $sql .= sprintf(' ORDER BY photo.createdAt %s', $way);
                    break;
            }
        } else {
            $sql .= sprintf(' ORDER BY photo.createdAt %s', $way);
        }

        $query = $em->createQuery($sql)
            ->setParameters($parameters)
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $count = count($paginator);

        $photos = null;
        $pagination = null;
        if ($count > 0) {
            $photos = array();
            foreach($paginator as $photo) {
                $photos[] = $photo;
            }

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_venue_photos');
        }

        return PaginationTools::getPaginationArray($photos, $pagination);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get a Venue's photos",
     *  output="AdEntify\CoreBundle\Entity\Tag",
     *  section="Venue"
     * )
     *
     * @View(serializerGroups={"list"})
     *
     */
    public function getTagsAction($id)
    {
        return $this->getDoctrine()->getManager()
            ->createQuery("SELECT tag
                           FROM AdEntify\CoreBundle\Entity\Tag tag
                           WHERE tag.venue = :venue_id
                           AND tag.visible = true
                           AND tag.deletedAt IS NULL
                           AND tag.censored = false
                           AND tag.validationStatus != :denied")
            ->setParameters(array(
                ':venue_id' => $id,
                ':denied' => Tag::VALIDATION_DENIED
            ))
            ->useQueryCache(false)
            ->getResult();
    }

    /**
     * Get form for Venue
     *
     * @param null $venue
     * @return mixed
     */
    protected function getForm($venue = null)
    {
        return $this->createForm(new VenueType(), $venue);
    }

    protected function addProductsToVenue($venue, $venueRequest, $em) {
        if ($venue && is_array($venueRequest) && array_key_exists('products', $venueRequest) && count($venueRequest['products']) > 0) {
            foreach ($venueRequest['products'] as $productId) {
                $found = count($venue->getProducts()) > 0;
                foreach ($venue->getProducts() as $product) {
                    if ($product->getId() == $productId) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $product = $em->getRepository('AdEntifyCoreBundle:Product')->find($productId);
                    if ($product)
                        $venue->addProduct($product);
                }
            }
        }
    }
}