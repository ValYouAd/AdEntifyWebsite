<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 03/05/2013
 * Time: 14:48
 * To change this template use File | Settings | File Templates.
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

use AdEntify\CoreBundle\Entity\OAuthUserInfo;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class OAuthUserInfoController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("OAuthUserInfo")
 */
class OAuthUserInfoController extends FosRestController
{
    /**
     * @View(serializerGroups={"details"})
     */
    public function cgetAction()
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $token = $this->container->get('security.context')->getToken();
            if ($token->getUser()) {
                return $token->getUser()->getOAuthUserInfos();
            } else {
                return null;
            }
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @param $id
     *
     * @View(serializerGroups={"details"})
     */
    public function deleteAction($id)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $em = $this->getDoctrine()->getManager();
            $user = $this->container->get('security.context')->getToken()->getUser();

            $service = $em->getRepository('AdEntifyCoreBundle:OAuthUserInfo')->findOneBy(array(
                'user' => $user->getId(),
                'id' => $id
            ));
            if ($service) {
                $em->remove($service);
                $em->flush();

                return true;
            } else {
                throw new NotFoundHttpException('Service not found');
            }
        } else {
            throw new HttpException(401);
        }
    }
}