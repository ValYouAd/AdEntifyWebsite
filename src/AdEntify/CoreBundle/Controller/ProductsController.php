<?php

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

    /**
     * @View()
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $product = new Product();
        $form = $this->getForm($product);
        $form->bind($request);
        if ($form->isValid()) {
            // Add venue products
            if ($product->getPurchaseUrl()) {
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
    }

    /**
     * Get form for Venue
     *
     * @param null $venue
     * @return mixed
     */
    protected function getForm($product = null)
    {
        return $this->createForm(new ProductType(), $product);
    }
}
