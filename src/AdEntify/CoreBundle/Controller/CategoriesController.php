<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/06/2013
 * Time: 11:46
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\Tag;
use AdEntify\CoreBundle\Form\PhotoType;
use AdEntify\CoreBundle\Util\PaginationTools;
use AdEntify\CoreBundle\Util\UserCacheManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection;

use AdEntify\CoreBundle\Entity\Category;
use AdEntify\CoreBundle\Entity\Photo;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class CategoriesController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("Category")
 */
class CategoriesController extends FosRestController
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get a collection of categories",
     *  output="AdEntify\CoreBundle\Entity\Category",
     *  section="Category"
     * )
     *
     * @View(serializerGroups={"list"})
     * @QueryParam(name="locale", default="en")
     */
    public function cgetAction($locale = 'en')
    {
        return $this->getDoctrine()->getManager()
            ->createQuery("SELECT category FROM AdEntify\CoreBundle\Entity\Category category ORDER BY category.displayOrder")
            ->useQueryCache(false)
            ->useResultCache(true, null, 'categories'.$locale)
            ->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale)
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_FALLBACK, 1)
            ->getResult();
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get a category",
     *  output="AdEntify\CoreBundle\Entity\Category",
     *  section="Category"
     * )
     *
     * @View(serializerGroups={"details"})
     * @QueryParam(name="locale", default="en")
     * @return Category
     */
    public function getAction($slug, $locale = 'en')
    {
        return $this->getDoctrine()->getManager()
            ->createQuery("SELECT category FROM AdEntify\CoreBundle\Entity\Category category WHERE category.slug = :slug")
            ->setParameter(':slug', $slug)
            ->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale)
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_FALLBACK, 1)
            ->useQueryCache(false)
            ->useResultCache(false)
            ->getOneOrNullResult();
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get category's photos",
     *  output="AdEntify\CoreBundle\Entity\Photo",
     *  section="Category"
     * )
     *
     * @View(serializerGroups={"list"})
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="30")
     * @QueryParam(name="brands", requirements="\d+", default="null")
     * @QueryParam(name="places", requirements="\d+", default="null")
     * @QueryParam(name="people", requirements="\d+", default="null")
     * @QueryParam(name="orderBy", default="null")
     */
    public function getPhotosAction($slug, $page, $limit, $brands, $places, $people, $orderBy)
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

        $parameters = array(
            ':status' => Photo::STATUS_READY,
            ':visibilityScope' => Photo::SCOPE_PUBLIC,
            ':facebookFriendsIds' => $facebookFriendsIds,
            ':followedBrands' => $followedBrands,
            ':followings' => $followings,
            ':denied' => Tag::VALIDATION_DENIED,
            ':slug' => $slug
        );

        $tagClause = '';
        $joinSide = 'LEFT';
        if ($brands == 1) {
            $tagClause = ' AND tag.brand IS NOT NULL';
            $joinSide = 'INNER';
        }
        if ($places == 1) {
            $tagClause = ' AND tag.venue IS NOT NULL';
            $joinSide = 'INNER';
        }
        if ($people == 1) {
            $tagClause = ' AND tag.person IS NOT NULL';
            $joinSide = 'INNER';
        }

        $sql = 'SELECT photo, tag FROM AdEntify\CoreBundle\Entity\Photo photo
            ' . $joinSide . ' JOIN photo.tags tag WITH (tag IS NULL OR (tag.visible = true AND tag.deletedAt IS NULL
              AND tag.censored = false AND tag.validationStatus != :denied' . $tagClause .'))
            INNER JOIN photo.owner owner LEFT JOIN tag.brand brand LEFT JOIN photo.categories category
            WHERE photo.status = :status AND photo.deletedAt IS NULL AND (photo.visibilityScope = :visibilityScope
            OR (owner.facebookId IS NOT NULL AND owner.facebookId IN (:facebookFriendsIds)) OR owner.id IN (:followings)
            OR brand.id IN (:followedBrands))
            AND category.slug = :slug';

        // Order by
        if ($orderBy) {
            switch ($orderBy) {
                case 'mostLiked':
                    $sql .= ' ORDER BY photo.likesCount DESC';
                    break;
                case 'oldest':
                    $sql .= ' ORDER BY photo.createdAt ASC';
                    break;
                case 'mostRecent':
                default:
                    $sql .= ' ORDER BY photo.createdAt DESC';
                    break;
            }
        } else {
            $sql .= ' ORDER BY photo.createdAt DESC';
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

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_category_photos', array(
                'slug' => $slug
            ));
        }

        return PaginationTools::getPaginationArray($photos, $pagination);
    }
}