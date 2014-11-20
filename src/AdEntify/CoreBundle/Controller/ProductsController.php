<?php

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Form\ProductType;
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

use AdEntify\CoreBundle\Entity\Product;
use AdEntify\CoreBundle\Entity\Tag;
use AdEntify\CoreBundle\Entity\Photo;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\HttpException;
use AdEntify\CoreBundle\Util\UserCacheManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
use AdEntify\CoreBundle\Util\PaginationTools;

/**
 * Class ProductsController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("Product")
 */
class ProductsController extends FosRestController
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get a collection of all products",
     *  output="AdEntify\CoreBundle\Entity\Product",
     *  section="Product"
     * )
     *
     * @View(serializerGroups={"list"})
     */
    public function cgetAction()
    {
        return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Product')->findAll();
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get a Product",
     *  output="AdEntify\CoreBundle\Entity\Product",
     *  section="Product"
     * )
     *
     * @View(serializerGroups={"details"})
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

        $product = $em->getRepository('AdEntifyCoreBundle:Product')->find($id);
        if ($product) {
            $lastPhoto = $em->createQuery('SELECT photo
                                           FROM AdEntifyCoreBundle:Photo photo
                                           LEFT JOIN photo.tags tag INNER JOIN photo.owner owner LEFT JOIN tag.brand brand LEFT JOIN tag.product product
                                           WHERE product.id = :productId AND photo.status = :status AND photo.deletedAt IS NULL
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
                    ':productId' => $product->getId(),
                ))
                ->setMaxResults(1)
                ->getOneOrNullResult();

            $product->setLastPhoto($lastPhoto);
            return $product;
        } else
            throw new HttpException(404);
    }

    /**
     * @param $query
     * @param $providers
     * @param int $page
     * @param int $limit
     * @param $brandId
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Search a product with a query (keyword)",
     *  output="AdEntify\CoreBundle\Entity\Product",
     *  section="Product"
     * )
     *
     * @QueryParam(name="query")
     * @QueryParam(name="providers", requirements="[a-z\s]+")
     * @QueryParam(name="brandId", requirements="\d+", default="0")
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="10")
     * @View(serializerGroups={"list"})
     */
    public function getSearchAction($query, $providers, $page, $limit, $brandId = 0)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->getRepository('AdEntifyCoreBundle:Product')->createQueryBuilder('p');
        $qb->where('p.name LIKE :query');
        $parameters = array(
            ':query' => '%'.$query.'%'
        );
//        echo sprintf('("%s")', join('", "', explode(' ', $providers)));
        if (!empty($providers)) {
            $qb = $em->getRepository('AdEntifyCoreBundle:ProductProvider')->createQueryBuilder('pp');
            $qb->where('pp.providerKey IN (:providers)');
            $parameters = array(
                ':providers' => explode(' ', $providers)
            );

                /*$qb->innerJoin('p.productProvider', 'pp', 'WITH', 'p.productProvider IN :provider');
                foreach($productProviders as $productProvider)
                    $productProvidersId[] = $productProvider->getId();
                $parameters[':provider'] = join('", "', $productProvidersId);
                echo join('", "', $productProvidersId);die;*/
        }

        if ($brandId > 0) {
            $qb->andWhere('p.brand = :brandId');
            $parameters['brandId'] = $brandId;
        }

        $qb->setParameters($parameters);

        return $qb->setMaxResults($limit)->setFirstResult(($page - 1) * $limit)->getQuery()->getResult();
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Post a Product",
     *  input="AdEntify\CoreBundle\Form\ProductType",
     *  output="AdEntify\CoreBundle\Entity\Product",
     *  section="Product",
     *  statusCodes={
     *         200="Returned when successful",
     *         401="Returned when authentication is required",
     *     }
     * )
     *
     * @View(serializerGroups={"details"})
     */
    public function postAction(Request $request)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $em = $this->getDoctrine()->getManager();
            $product = new Product();
            $form = $this->getForm($product);
            $form->bind($request);
            if ($form->isValid()) {
                // Add venue products
                if ($product->getPurchaseUrl()) {
                    $product->setPurchaseUrl(CommonTools::addScheme($product->getPurchaseUrl()));

                    $shortUrl = $em->getRepository('AdEntifyCoreBundle:ShortUrl')->createShortUrl($product->getPurchaseUrl());
                    if ($shortUrl)
                        $product->setPurchaseShortUrl($shortUrl)->setLink($this->generateUrl('redirect_url', array(
                            'id' => $shortUrl->getBase62Id()
                        )));
                }
                $em->persist($product);
                $em->flush();

                return $product;
            } else {
                return $form;
            }
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get a Product's photos",
     *  output="AdEntify\CoreBundle\Entity\Photo",
     *  section="Product"
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
            ':product_id' => $id,
        );

        $sql = sprintf('SELECT photo
                        FROM AdEntify\CoreBundle\Entity\Photo photo
                        LEFT JOIN photo.tags tag
                        INNER JOIN photo.owner owner
                        LEFT JOIN tag.brand brand %s
                        WHERE tag.product = :product_id
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

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_product_photos');
        }

        return PaginationTools::getPaginationArray($photos, $pagination);
    }

    /**
     * Get form for Product
     *
     * @param null $product
     * @return mixed
     */
    protected function getForm($product = null)
    {
        return $this->createForm(new ProductType(), $product);
    }
}