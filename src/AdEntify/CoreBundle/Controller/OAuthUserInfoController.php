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
     * @View()
     */
    public function cgetAction()
    {
        $token = $this->container->get('security.context')->getToken();
        if ($token->getUser()) {
            return $token->getUser()->getOAuthUserInfos();
        } else {
            return null;
        }
    }

    /**
     * @param $id
     *
     * @View()
     */
    public function deleteAction($id)
    {
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
    }
}