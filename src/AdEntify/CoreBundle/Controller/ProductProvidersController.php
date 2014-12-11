<?php
/**
 * Created by PhpStorm.
 * User: huas
 * Date: 20/11/2014
 * Time: 16:23
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\UserProductProvider;
use AdEntify\CoreBundle\Form\UserProductProviderType;
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
     *  description="Get current user's products providers",
     *  output="AdEntify\CoreBundle\Entity\ProductProvider",
     *  section="ProductProviders"
     * )
     *
     * @View(serializerGroups={"list"})
     */
    public function getCurrentUserAction()
    {
        if ($this->getUser()) {
            $providers = $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:UserProductProvider')->findByUser($this->getUser());
            if (empty($providers))
            {
                $defaultProviders = $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:ProductProvider')->findBy(array(
                    'providerKey' => array('adentify', 'shopsense')
                ));
                foreach ($defaultProviders as $defaultProvider)
                {
                    $userProductProvider = new UserProductProvider();
                    $userProductProvider->setUsers($this->getUser());
                    $userProductProvider->setProductProviders($defaultProvider);
                    $this->getDoctrine()->getManager()->persist($userProductProvider);
                    $this->getDoctrine()->getManager()->flush();
                    $providers[] = $userProductProvider;
                }
            }
            return $providers;
        } else
            throw new HttpException(403);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Update the current user's product provider",
     *  input="AdEntify\CoreBundle\Form\UserProductProviderType",
     *  output="AdEntify\CoreBundle\Entity\UserProductProvider",
     *  statusCodes={
     *      200="Returned if the user's product provider is updated",
     *  },
     *  section="ProductProviders"
     * )
     *
     * @View(serializerGroups={"list"})
     */
    public function putAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $userProductProvider = $em->getRepository('AdEntifyCoreBundle:UserProductProvider')->findOneBy(array(
            'productProviders' => $id
        ));
        // Check user connecte
        if ($this->getUser() == $userProductProvider->getUsers())
        {
            // Load form type
            $form = $this->getForm($userProductProvider);

            // Handle request
            $form->bind($request);

            if ($form->isValid())
            {
                // MAJ du user product provider pour le user courant
                $productProvider = $em->getRepository('AdEntifyCoreBundle:ProductProvider')->find($request->get('userProductProvider')['productProviders']);
                $userProductProvider->setProductProviders($productProvider);

                $user = $em->getRepository('AdEntifyCoreBundle:User')->find($this->getUser()->getId());
                $userProductProvider->setUsers($user);

                $em->persist($userProductProvider);
                $em->flush();
                // return userproductprovider a jour
                return $userProductProvider;
            }
            else
                return $form;
        }
        else
            throw new HttpException(401);
    }

    /**
     * Get form for productProvider
     *
     * @param null $userProductProvider
     * @return mixed
     */
    protected function getForm($userProductProvider = null)
    {
        return $this->createForm(new UserProductProviderType(), $userProductProvider);
    }
}