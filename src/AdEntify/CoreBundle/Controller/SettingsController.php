<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 02/08/2013
 * Time: 15:22
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection;

/**
 * Class SettingsController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 */
class SettingsController extends FosRestController
{
    /**
     * @View()
     */
    public function getUserServicesAction()
    {
        $em = $this->getDoctrine()->getManager();
        $securityContext = $this->container->get('security.context');
        $user = $securityContext->getToken()->getUser();

        $services = $em->getRepository('AdEntifyCoreBundle:OAuthUserInfo')->findBy(array(
            'user' => $user->getId()
        ));
        $connectedServices = array();
        if ($services) {
            foreach($services as $service) {
                $connectedServices[] = array(
                    'id' => $service->getId(),
                    'service_name' => $service->getServiceName(),
                );
            }
        }
        if ($securityContext->isGranted('ROLE_FACEBOOK')) {
            $connectedServices[] = array(
                'service_name' => 'Facebook',
                'cant_delete' => true
            );
        }
        return $connectedServices;
    }
}