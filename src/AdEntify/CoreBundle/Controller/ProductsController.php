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
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
     * @View()
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
     * @View()
     */
    public function getAction($id)
    {
        return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Product')->find($id);
    }

    /**
     * @param $query
     * @param int $page
     * @param int $limit
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Search a product with a query (keyword)",
     *  output="AdEntify\CoreBundle\Entity\Product",
     *  section="Product"
     * )
     *
     * @QueryParam(name="brandId", requirements="\d+", default="0")
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="10")
     * @View()
     */
    public function getSearchAction($query, $page, $limit, $brandId = 0)
    {
        $qb = $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Product')->createQueryBuilder('p');
        $qb->where('p.name LIKE :query');

        $parameters = array(
            ':query' => '%'.$query.'%'
        );

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
     *  section="Product"
     * )
     *
     * @View()
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
