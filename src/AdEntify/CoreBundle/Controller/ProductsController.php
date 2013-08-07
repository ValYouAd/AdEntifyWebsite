<?php

namespace AdEntify\CoreBundle\Controller;

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
     * @View()
     */
    public function cgetAction()
    {
        return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Product')->findAll();
    }

    /**
     * @View()
     */
    public function getAction($id)
    {
        return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Product')->find($id);
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
        return $this->getDoctrine()->getManager()->createQuery('SELECT product FROM AdEntify\CoreBundle\Entity\Product product
            WHERE product.name LIKE :query')
            ->setParameter(':query', '%'.$query.'%')
            ->setMaxResults($limit)
            ->getResult();
    }
}
