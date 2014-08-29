<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 18/04/2013
 * Time: 17:57
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\Action;
use AdEntify\CoreBundle\Entity\Photo;
use AdEntify\CoreBundle\Form\BrandType;
use AdEntify\CoreBundle\Model\Thumb;
use AdEntify\CoreBundle\Util\FileTools;
use AdEntify\CoreBundle\Util\PaginationTools;
use AdEntify\CoreBundle\Util\UserCacheManager;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection;

use AdEntify\CoreBundle\Entity\Brand;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class BrandsController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("Brand")
 */
class BrandsController extends FosRestController
{
    /**
     * Get a collection of all brands
     *
     * @return ArrayCollection
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Get a collection of brands",
     *  output="AdEntify\CoreBundle\Entity\Brand",
     *  section="Brand"
     * )
     *
     * @View(serializerGroups={"list"})
     * @QueryParam(name="orderBy", default="null")
     * @QueryParam(name="way", requirements="DESC|ASC", default="ASC")
     */
    public function cgetAction($orderBy = null, $way = 'ASC')
    {
        $qb = $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Brand')->createQueryBuilder('b');
        $qb->where('b.validated = :validated')
            ->setParameters(array(
                ':validated' => true
            ));

        switch ($orderBy) {
            case 'number-of-tags':
                $qb->orderBy('b.tagsCount', $way);
                break;
            case 'name':
            default:
                $qb->orderBy('b.name', $way);
                break;
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Get brand by slug
     *
     * @View(serializerGroups={"details"})
     *
     * @ApiDoc(
     *  resource=true,
     *  output="AdEntify\CoreBundle\Entity\Brand",
     *  section="Brand"
     * )
     *
     * @return Brand
     */
    public function getAction($slug)
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

        $brand = $em->getRepository('AdEntifyCoreBundle:Brand')->findOneBy(array(
            'slug' => $slug,
            'validated' => true
        ));
        if ($brand) {
            $lastPhoto = $em->createQuery('SELECT photo
                                           FROM AdEntifyCoreBundle:Photo photo
                                           LEFT JOIN photo.tags tag INNER JOIN photo.owner owner LEFT JOIN tag.brand brand
                                           WHERE brand.id = :brandId AND photo.status = :status AND photo.deletedAt IS NULL
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
                    ':brandId' => $brand->getId(),
                ))
                ->setMaxResults(1)
                ->getOneOrNullResult();

            $brand->setLastPhoto($lastPhoto);

            $count = $em->createQuery('SELECT COUNT(DISTINCT photo)
                                       FROM AdEntifyCoreBundle:Photo photo
                                       LEFT JOIN photo.tags tag INNER JOIN photo.owner owner LEFT JOIN tag.brand brand
                                       WHERE brand.id = :brandId AND photo.status = :status AND photo.deletedAt IS NULL
                                          AND (photo.visibilityScope = :visibilityScope
                                            OR (owner.facebookId IS NOT NULL AND owner.facebookId IN (:facebookFriendsIds))
                                            OR owner.id IN (:followings)
                                            OR brand.id IN (:followedBrands))
                                       ')
                ->setParameters(array(
                    ':status' => Photo::STATUS_READY,
                    ':visibilityScope' => Photo::SCOPE_PUBLIC,
                    ':facebookFriendsIds' => $facebookFriendsIds,
                    ':followings' => $followings,
                    ':followedBrands' => $followedBrands,
                    ':brandId' => $brand->getId(),
                ))
                ->getSingleScalarResult();

            $randomPhoto = $em->createQuery('SELECT DISTINCT photo
                                             FROM AdEntifyCoreBundle:Photo photo
                                             LEFT JOIN photo.tags tag INNER JOIN photo.owner owner LEFT JOIN tag.brand brand
                                             WHERE brand.id = :brandId AND photo.status = :status AND photo.deletedAt IS NULL
                                                AND (photo.visibilityScope = :visibilityScope
                                                  OR (owner.facebookId IS NOT NULL AND owner.facebookId IN (:facebookFriendsIds))
                                                  OR owner.id IN (:followings)
                                                  OR brand.id IN (:followedBrands))
                                             ')
                ->setParameters(array(
                    ':status' => Photo::STATUS_READY,
                    ':visibilityScope' => Photo::SCOPE_PUBLIC,
                    ':facebookFriendsIds' => $facebookFriendsIds,
                    ':followings' => $followings,
                    ':followedBrands' => $followedBrands,
                    ':brandId' => $brand->getId(),
                ))
                ->setFirstResult(rand(0, $count - 1))
                ->setMaxResults(1)
                ->getOneOrNullResult();

            $brand->setRandomPhoto($randomPhoto);
            return $brand;
        } else
            throw new HttpException(404);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Post a Brand",
     *  input="AdEntify\CoreBundle\Form\BrandType",
     *  output="AdEntify\CoreBundle\Entity\Brand",
     *  section="Brand"
     * )
     *
     * @View(serializerGroups={"details"})
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $brand = new Brand();
        $form = $this->getForm($brand);
        $form->bind($request);
        if ($form->isValid()) {
            $found = $em->getRepository('AdEntifyCoreBundle:Brand')->findOneBy(array('name' => $brand->getName()));
            if ($found) {
                return $found;
            }

            if ($brand->getOriginalLogoUrl()) {
                $thumb = new Thumb();
                $filename = urlencode($brand->getName()).'.jpg';
                $thumb->setOriginalPath($brand->getOriginalLogoUrl());
                $thumb->addThumbSize(FileTools::LOGO_TYPE_LARGE);
                $thumb->addThumbSize(FileTools::LOGO_TYPE_MEDIUM);
                $thumb->addThumbSize(FileTools::LOGO_TYPE_SMALLL);
                $thumbs = $this->container->get('ad_entify_core.thumb')->generateBrandLogoThumb($thumb, $filename);
                $brand->setSmallLogoUrl($thumbs[FileTools::LOGO_TYPE_SMALLL]['filename']);
                $brand->setMediumLogoUrl($thumbs[FileTools::LOGO_TYPE_MEDIUM]['filename']);
                $brand->setLargeLogoUrl($thumbs[FileTools::LOGO_TYPE_LARGE]['filename']);
            }

            $brand->setValidated(true);

            $em->persist($brand);
            $em->flush();

            return $brand;
        } else {
            return $form;
        }
    }

    /**
     * GET all categories by brand slug
     *
     * @View(serializerGroups={"list"})
     * @QueryParam(name="locale", default="en")
     *
     * @param $id
     * @return ArrayCollection|null
     */
    public function getCategoriesAction($slug, $locale = 'en')
    {
        return $this->getDoctrine()->getManager()
            ->createQuery("SELECT category FROM AdEntify\CoreBundle\Entity\Category category
                LEFT JOIN category.brands brand WHERE brand.slug = :slug AND category.visible = 1")
            ->setParameter('slug', $slug)
            ->useQueryCache(false)
            ->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale)
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_FALLBACK, 1)
            ->getResult();
    }

    /**
     * @param $query
     * @param int $page
     * @param int $limit
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Search a brand with a query (keyword)",
     *  output="AdEntify\CoreBundle\Entity\Brand",
     *  section="Brand"
     * )
     *
     * @QueryParam(name="query")
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="10")
     *
     * @View(serializerGroups={"list"})
     */
    public function getSearchAction($query, $page, $limit)
    {
        if (empty($query)) {
            throw new HttpException(500);
        }

        $query = $this->getDoctrine()->getManager()->createQuery('SELECT brand FROM AdEntify\CoreBundle\Entity\Brand brand
            WHERE brand.name LIKE :query AND brand.validated = 1')
	    ->setParameter(':query', '%'.$query.'%')
            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit);

        $paginator = new Paginator($query, $fetchJoinCollection = false);
        $count = count($paginator);

        $brands = null;
        $pagination = null;
        if ($count > 0) {
            $brands = array();
            foreach($paginator as $brand) $brands[] = $brand;
            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_brand_search', array(
                'query' => $query
            ));
        }

        return PaginationTools::getPaginationArray($brands, $pagination);
    }

    /**
     * @View(serializerGroups={"list"})
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Get a collection of followers (user) for a brand",
     *  output="AdEntify\CoreBundle\Entity\User",
     *  section="Brand"
     * )
     *
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="10")
     *
     * @param $slug
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function getFollowersAction($slug, $page = 1, $limit = 10)
    {
	$securityContext = $this->container->get('security.context');
	$query = $this->getDoctrine()->getManager()->createQuery('SELECT user, (SELECT COUNT(u.id) FROM AdEntifyCoreBundle:User u
		LEFT JOIN u.followings f WHERE u.id = :currentUserId AND f.id = user.id) as followed FROM AdEntify\CoreBundle\Entity\User user
            JOIN user.followedBrands brand WHERE brand.slug = :slug ORDER BY user.followersCount DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->setParameters(array(
		'slug' => $slug,
		'currentUserId' => $securityContext->isGranted('IS_AUTHENTICATED_FULLY') ? $this->container->get('security.context')->getToken()->getUser()->getId() : 0
            ));

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $pagination = null;
        $users = null;
        $count = count($paginator);
        if ($count > 0) {
            $users = array();
	    foreach ($paginator as $entry) {
		$user = $entry[0];
		$user->setFollowed($entry['followed'] > 0 ? true : false);
                $users[] = $user;
            }

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_brand_followers', array(
                'slug' => $slug
            ));
        }

        return PaginationTools::getPaginationArray($users, $pagination);
    }

    /**
     * @View(serializerGroups={"list"})
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Get a collection of followings (User) for a brand",
     *  output="AdEntify\CoreBundle\Entity\User",
     *  section="Brand"
     * )
     *
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="10")
     *
     * @param $slug
     * @param $page
     * @param $limit
     * @return mixed
     */
    public function getFollowingsAction($slug, $page = 1, $limit = 10)
    {
        $brand = $this->getAction($slug);
        if (!$brand)
            throw new HttpException(404);

	$securityContext = $this->container->get('security.context');
	$query = $this->getDoctrine()->getManager()->createQuery('SELECT user, (SELECT COUNT(u.id) FROM AdEntifyCoreBundle:User u
		LEFT JOIN u.followings f WHERE u.id = :currentUserId AND f.id = user.id) as followed FROM AdEntify\CoreBundle\Entity\User user
            LEFT JOIN user.followers follower WHERE follower.id = :brandUserId ORDER BY user.followersCount DESC')
	    ->setParameters(array(
		'brandUserId' => $brand->getAdmin() ? $brand->getAdmin()->getId() : 0,
		'currentUserId' => $securityContext->isGranted('IS_AUTHENTICATED_FULLY') ? $this->container->get('security.context')->getToken()->getUser()->getId() : 0
	    ))
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $pagination = null;
        $users = null;
        $count = count($paginator);
        if ($count > 0) {
            $users = array();
	    foreach ($paginator as $entry) {
		$user = $entry[0];
		$user->setFollowed($entry['followed'] > 0 ? true : false);
                $users[] = $user;
            }

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_brand_followings', array(
                'slug' => $slug
            ));
        }

        return PaginationTools::getPaginationArray($users, $pagination);
    }

    /**
     * @param $slug
     * @return ArrayCollection|null
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Get a collection of products for the brand",
     *  output="AdEntify\CoreBundle\Entity\Product",
     *  section="Brand"
     * )
     *
     * @View(serializerGroups={"list"})
     */
    public function getProductsAction($slug)
    {
        $brand = $this->getAction($slug);
        if ($brand) {
            return $brand->getProducts();
        } else {
            throw new NotFoundHttpException('Brand not found');
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get a collection of photos for the brand",
     *  output="AdEntify\CoreBundle\Entity\Photo",
     *  section="Brand"
     * )
     *
     * @View(serializerGroups={"list"})
     *
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="20")
     *
     * @param $slug
     * @param $page
     * @param $limit
     * @return ArrayCollection|null
     */
    public function getPhotosAction($slug, $page = 1, $limit = 20)
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

        if ($user) {
            // Get friends list (id) array
            $facebookFriendsIds = UserCacheManager::getInstance()->getUserObject($user, UserCacheManager::USER_CACHE_KEY_FB_FRIENDS);
            if (!$facebookFriendsIds) {
                $facebookFriendsIds = $em->getRepository('AdEntifyCoreBundle:User')->refreshFriends($user, $this->container->get('fos_facebook.api'));
                UserCacheManager::getInstance()->setUserObject($user, UserCacheManager::USER_CACHE_KEY_FB_FRIENDS, $facebookFriendsIds, UserCacheManager::USER_CACHE_TTL_FB_FRIENDS);
            }

            // Get brands ids
            $followings = UserCacheManager::getInstance()->getUserObject($user, UserCacheManager::USER_CACHE_KEY_FOLLOWINGS);
            if (!$followings) {
                $followings = $em->getRepository('AdEntifyCoreBundle:User')->getFollowingsIds($user, 0);
                UserCacheManager::getInstance()->setUserObject($user, UserCacheManager::USER_CACHE_KEY_FOLLOWINGS, $followings, UserCacheManager::USER_CACHE_TTL_FOLLOWING);
            }
        }

        $query = $em->createQuery('SELECT photo, tag FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.tags tag LEFT JOIN photo.owner owner LEFT JOIN tag.brand tagBrand LEFT JOIN tag.brand brand
                WHERE brand.validated = 1 AND (brand.slug = :slug OR tagBrand.slug = :slug) AND photo.status = :status AND photo.deletedAt IS NULL
                AND photo.tagsCount > 0 AND (photo.visibilityScope = :visibilityScope OR (owner.facebookId IS NOT NULL
                AND owner.facebookId IN (:facebookFriendsIds)) OR owner.id IN (:followings)) ORDER BY photo.createdAt DESC')
            ->setParameters(array(
                ':status' => Photo::STATUS_READY,
                ':visibilityScope' => Photo::SCOPE_PUBLIC,
                ':facebookFriendsIds' => $facebookFriendsIds,
                ':followings' => $followings,
                ':slug' => $slug
            ));

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $count = count($paginator);

        $photos = null;
        $pagination = null;
        if ($count > 0) {
            $photos = array();
            foreach($paginator as $photo) {
                $photos[] = $photo;
            }

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_brand_photos');
        }

        return PaginationTools::getPaginationArray($photos, $pagination);
    }

    /**
     * @View(serializerGroups={"details"})
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Check if current user follow a brand",
     *  section="Brand"
     * )
     *
     * @param $id
     */
    public function getIsFollowedAction($slug)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $follower = $this->container->get('security.context')->getToken()->getUser();
            $followed = $this->getDoctrine()->getManager()->createQuery('SELECT COUNT(u.id) FROM AdEntify\CoreBundle\Entity\User u
                LEFT JOIN u.followedBrands brand WHERE u.id = :userId AND brand.slug = :slug')
                ->setParameters(array(
                    'userId' => $follower->getId(),
                    'slug' => $slug
                ))
                ->getSingleScalarResult() > 0;

            return array(
                'followed' => $followed
            );
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @param $slug brand slug
     * @return mixed
     *
     * @ApiDoc(
     *  resource=true,
     *  description="POST a new follower for a brand",
     *  section="Brand"
     * )
     *
     * @View(serializerGroups={"details"})
     */
    public function postFollowerAction($slug)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $em = $this->getDoctrine()->getManager();

            $follower = $this->container->get('security.context')->getToken()->getUser();
            $brand = $this->getAction($slug);
            $isFolowed = $this->getIsFollowedAction($slug);
            if ($brand && !$isFolowed['followed']) {
                $brand->addFollower($follower);
                $brand->setFollowersCount($brand->getFollowersCount() + 1);
                $follower->setFollowedBrandsCount($follower->getFollowedBrandsCount() + 1);
                $em->merge($follower);
                $em->merge($brand);

                $em->getRepository('AdEntifyCoreBundle:Action')->createAction(Action::TYPE_BRAND_FOLLOW,
                    $follower, null, null, Action::VISIBILITY_PUBLIC, null,
                    null, false, 'followBrand', null, null, $brand);

                $em->flush();

                // Empty followings cache
                UserCacheManager::getInstance()->deleteUserObject($follower, UserCacheManager::USER_CACHE_KEY_BRAND_FOLLOWINGS);

                return array(
                    'followed' => true
                );
            } else {
                $brand->removeFollower($follower);
                $brand->setFollowersCount($brand->getFollowersCount() - 1);
                $follower->setFollowedBrandsCount($follower->getFollowedBrandsCount() - 1);
                $em->merge($follower);
                $em->merge($brand);
                $em->flush();

                // Empty followings cache
                UserCacheManager::getInstance()->deleteUserObject($follower, UserCacheManager::USER_CACHE_KEY_BRAND_FOLLOWINGS);

                return array(
                    'followed' => false
                );
            }
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="GET rewards by user ID",
     *  output="AdEntify\CoreBundle\Entity\Reward",
     *  section="Brand",
     * parameters={
     *   {"name"="slug", "dataType"="string", "required"=true, "description"="brand slug"}
     * }
     * )
     *
     * @param $slug
     *
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="10")
     *
     * @View(serializerGroups={"list"})
     */
    public function getRewardsAction($slug, $page = 1, $limit = 10)
    {
        $query = $this->getDoctrine()->getManager()->createQuery('SELECT reward FROM AdEntifyCoreBundle:Reward reward
            LEFT JOIN reward.brand brand WHERE brand.slug = :slug ORDER BY reward.type')
            ->setParameters(array(
                'slug'=> $slug
            ))
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $count = count($paginator);

        $rewards = null;
        $pagination = null;
        if ($count > 0) {
            $rewards = array();
            foreach ($paginator as $reward)
                $rewards[] = $reward;

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this,
                'api_v1_get_brand_rewards', array(
                    'slug' => $slug
                ));
        }
        return PaginationTools::getPaginationArray($rewards, $pagination);
    }

    /**
     * Get form for Brand
     *
     * @param null $brand
     * @return mixed
     */
    protected function getForm($brand = null)
    {
        return $this->createForm(new BrandType(), $brand);
    }
}
