<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 18/04/2013
 * Time: 17:57
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\Photo;
use AdEntify\CoreBundle\Util\PaginationTools;
use AdEntify\CoreBundle\Util\UserCacheManager;
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
     * @View()
     */
    public function cgetAction()
    {
        return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Brand')->findAll();
    }

    /**
     * Get brand by slug
     *
     * @View()
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
        return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Brand')->findOneBy(array(
            'slug' => $slug
        ));
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
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="10")
     * @View()
     */
    public function getSearchAction($query, $page, $limit)
    {
        return $this->getDoctrine()->getManager()->createQuery('SELECT brand FROM AdEntify\CoreBundle\Entity\Brand brand
            WHERE brand.name LIKE :query')
            ->setParameter(':query', '%'.$query.'%')
            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit)
            ->getResult();
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
     * @View()
     */
    public function getProductsAction($slug)
    {
        $brand = $this->getAction($slug);
        if ($brand) {
            return $brand->getProducts();
        } else {
            return null;
        }
    }

    /**
     * @View()
     *
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="20")
     *
     * @return ArrayCollection|null
     */
    public function getPhotosAction($slug, $page = 1, $limit = 20)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        // Get friends list (id) array
        $facebookFriendsIds = UserCacheManager::getInstance()->getUserObject($user, UserCacheManager::USER_CACHE_KEY_FB_FRIENDS);
        if (!$facebookFriendsIds) {
            $facebookFriendsIds = $em->getRepository('AdEntifyCoreBundle:User')->refreshFriends($user, $this->container->get('fos_facebook.api'));
            UserCacheManager::getInstance()->setUserObject($user, UserCacheManager::USER_CACHE_KEY_FB_FRIENDS, $facebookFriendsIds, UserCacheManager::USER_CACHE_TTL_FB_FRIENDS);
        }

        // Get followings ids
        $followings = UserCacheManager::getInstance()->getUserObject($user, UserCacheManager::USER_CACHE_KEY_FOLLOWINGS);
        if (!$followings) {
            $followings = $user->getFollowingsIds();
            UserCacheManager::getInstance()->setUserObject($user, UserCacheManager::USER_CACHE_KEY_FOLLOWINGS, $followings, UserCacheManager::USER_CACHE_TTL_FOLLOWING);
        }

        $count = $em->createQuery('SELECT count(photo.id) FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.tags tag LEFT JOIN photo.owner owner LEFT JOIN tag.product product LEFT JOIN product.brand brand
                WHERE brand.slug = :slug AND photo.status = :status AND photo.tagsCount > 0
                AND (photo.visibilityScope = :visibilityScope OR (owner.facebookId IS NOT NULL
                AND owner.facebookId IN (:facebookFriendsIds)) OR owner.id IN (:followings))')
            ->setParameters(array(
                ':status' => Photo::STATUS_READY,
                ':visibilityScope' => Photo::SCOPE_PUBLIC,
                ':facebookFriendsIds' => $facebookFriendsIds,
                ':followings' => $followings,
                ':slug' => $slug
            ))
            ->getSingleScalarResult();

        $photos = null;
        $pagination = null;
        if ($count > 0) {
            $photos = $em->createQuery('SELECT photo, tag FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.tags tag LEFT JOIN photo.owner owner LEFT JOIN tag.product product LEFT JOIN product.brand brand
                WHERE brand.slug = :slug AND photo.status = :status AND photo.tagsCount > 0 AND tag.visible = true
                AND tag.deletedAt IS NULL AND tag.censored = FALSE AND tag.waitingValidation = FALSE
                AND (photo.visibilityScope = :visibilityScope OR (owner.facebookId IS NOT NULL
                AND owner.facebookId IN (:facebookFriendsIds)) OR owner.id IN (:followings))
                ORDER BY photo.createdAt DESC')
                ->setParameters(array(
                    ':status' => Photo::STATUS_READY,
                    ':visibilityScope' => Photo::SCOPE_PUBLIC,
                    ':facebookFriendsIds' => $facebookFriendsIds,
                    ':followings' => $followings,
                    ':slug' => $slug
                ))
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getResult();

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_brand_photos');
        }

        return PaginationTools::getPaginationArray($photos, $pagination);
    }
}
