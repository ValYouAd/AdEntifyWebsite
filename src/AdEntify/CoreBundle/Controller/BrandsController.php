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
     * GET all categories by brand slug
     *
     * @View()
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
     * @View()
     */
    public function getSearchAction($query, $page, $limit)
    {
        $query = $this->getDoctrine()->getManager()->createQuery('SELECT brand FROM AdEntify\CoreBundle\Entity\Brand brand
            WHERE brand.name LIKE :query')
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
     * @View()
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
        $query = $this->getDoctrine()->getManager()->createQuery('SELECT user FROM AdEntify\CoreBundle\Entity\User user
            JOIN user.followedBrands brand WHERE brand.slug = :slug ORDER BY user.followersCount DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->setParameters(array(
                'slug' => $slug
            ));

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $pagination = null;
        $users = null;
        $count = count($paginator);
        if ($count > 0) {
            $users = array();
            foreach($paginator as $user) {
                $users[] = $user;
            }

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_brand_followers', array(
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
     * @View()
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
     * @View()
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
                $followings = $user->getFollowingsIds();
                UserCacheManager::getInstance()->setUserObject($user, UserCacheManager::USER_CACHE_KEY_FOLLOWINGS, $followings, UserCacheManager::USER_CACHE_TTL_FOLLOWING);
            }
        }

        $query = $em->createQuery('SELECT photo, tag FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.tags tag LEFT JOIN photo.owner owner LEFT JOIN tag.brand tagBrand LEFT JOIN tag.product product LEFT JOIN product.brand brand
                WHERE (brand.slug = :slug OR tagBrand.slug = :slug) AND photo.status = :status AND photo.tagsCount > 0
                AND (photo.visibilityScope = :visibilityScope OR (owner.facebookId IS NOT NULL
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
     * @View()
     *
     * @param $id
     */
    public function getIsFollowingAction($slug)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $follower = $this->container->get('security.context')->getToken()->getUser();
            return $this->getDoctrine()->getManager()->createQuery('SELECT COUNT(u.id) FROM AdEntify\CoreBundle\Entity\User u
                LEFT JOIN u.followedBrands brand WHERE u.id = :userId AND brand.slug = :slug')
                ->setParameters(array(
                    'userId' => $follower->getId(),
                    'slug' => $slug
                ))
                ->getSingleScalarResult() > 0 ? true : false;
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @param $slug brand slug
     * @return mixed
     *
     * @View()
     */
    public function postFollowerAction($slug)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $em = $this->getDoctrine()->getManager();

            $follower = $this->container->get('security.context')->getToken()->getUser();
            $brand = $this->getAction($slug);
            if ($brand && !$this->getIsFollowingAction($slug)) {
                $brand->addFollower($follower);
                $brand->setFollowersCount($brand->getFollowersCount() + 1);
                $follower->setFollowedBrandsCount($follower->getFollowedBrandsCount() + 1);
                $em->merge($follower);
                $em->merge($brand);
                $em->flush();
                return $follower;
            } else {
                $brand->removeFollower($follower);
                $brand->setFollowersCount($brand->getFollowersCount() - 1);
                $follower->setFollowedBrandsCount($follower->getFollowedBrandsCount() - 1);
                $em->merge($follower);
                $em->merge($brand);
                $em->flush();
            }
        } else {
            throw new HttpException(401);
        }
    }
}
