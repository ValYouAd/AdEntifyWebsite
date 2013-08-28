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
use Symfony\Component\HttpFoundation\Request;

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
     * @View()
     */
    public function cgetAction()
    {
        return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Brand')->findAll();
    }

    /**
     * @View()
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
     * @param int $limit
     *
     * @QueryParam(name="limit", default="10")
     * @View()
     */
    public function getSearchAction($query, $limit)
    {
        return $this->getDoctrine()->getManager()->createQuery('SELECT brand FROM AdEntify\CoreBundle\Entity\Brand brand
            WHERE brand.name LIKE :query')
            ->setParameter(':query', '%'.$query.'%')
            ->setMaxResults($limit)
            ->getResult();
    }

    /**
     * @param $slug
     * @return ArrayCollection|null
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
        $facebookFriendsIds = $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:User')->refreshFriends($user, $this->container->get('fos_facebook.api'));

        // Get followings ids
        $followings = $user->getFollowingsIds();

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
                AND tag.deleted_at IS NULL AND tag.censored = FALSE AND tag.waitingValidation = FALSE
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