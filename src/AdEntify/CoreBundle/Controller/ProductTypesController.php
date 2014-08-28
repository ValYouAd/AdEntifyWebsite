<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 09/01/2014
 * Time: 11:15
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Form\ProductType;
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
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ProductTypesController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("ProductType")
 */
class ProductTypesController extends FosRestController
{
    /**
     * @param $query
     * @param int $page
     * @param int $limit
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Search a product type with a query (keyword)",
     *  output="AdEntify\CoreBundle\Entity\ProductType",
     *  section="ProductType"
     * )
     *
     * @QueryParam(name="query")
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="10")
     * @View(serializerGroups={"list"})
     */
    public function getSearchAction($query, $page, $limit)
    {
        return $this->getDoctrine()->getManager()->createQuery('SELECT productType FROM AdEntifyCoreBundle:ProductType productType
            WHERE productType.name LIKE :query')
            ->setParameter(':query', '%'.$query.'%')
            ->setMaxResults($limit)
            ->setFirstResult(($page - 1) * $limit)
            ->getResult();
    }
} 