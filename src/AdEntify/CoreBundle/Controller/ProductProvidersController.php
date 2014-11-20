<?php
/**
 * Created by PhpStorm.
 * User: huas
 * Date: 20/11/2014
 * Time: 16:23
 */

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

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ProductProvidersController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("productproviders")
 */
class ProductProvidersController extends FosRestController
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get a user's products providers",
     *  output="AdEntify\CoreBundle\Entity\ProductProvider",
     *  section="ProductProviders"
     * )
     *
     * @View(serializerGroups={"details"})
     */
    public function getAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('AdEntifyCoreBundle:User')->find($id);
        if ($user) {
            $productProviders = $em->createQuery('SELECT pp
                                                  FROM AdEntifyCoreBundle:UsersProductProvider pp
                                                  WHERE pp.user = :id')
                ->setParameters(array(
                    ':id' => $user->getId(),
                ))
                ->getResult();

            return $productProviders;
        } else
            throw new HttpException(404);
    }
}