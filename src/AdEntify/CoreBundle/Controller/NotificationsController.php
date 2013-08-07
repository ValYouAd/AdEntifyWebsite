<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/07/2013
 * Time: 14:53
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\Photo;
use AdEntify\CoreBundle\Form\NotificationType;
use AdEntify\CoreBundle\Form\VenueType;
use Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection;

use AdEntify\CoreBundle\Entity\User;
use AdEntify\CoreBundle\Util\PaginationTools;

/**
 * Class NotificationsController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("Notification")
 */
class NotificationsController extends FOSRestController
{
    /**
     * @View()
     *
     * @return Notification
     */
    public function getAction($id)
    {
        return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Notification')->find($id);
    }

    /**
     * @View()
     */
    public function putAction($id, Request $request)
    {
        $notification = $this->getAction($id);
        if ($notification) {
            $user = $this->container->get('security.context')->getToken()->getUser();
            if ($notification->getOwner()->getId() == $user->getId()) {
                $form = $this->getForm($notification);
                $form->bind($request);
                if ($form->isValid()) {
                    $em = $this->getDoctrine()->getManager();
                    $em->merge($notification);
                    $em->flush();
                    return $notification;
                } else {
                    throw new \Exception($form->getErrorsAsString());
                }
            } else
                throw new ForbiddenHttpException();
        } else
            throw new HttpNotFoundException();
    }

    /**
     * Get form for notification
     *
     * @param null $notification
     * @return mixed
     */
    protected function getForm($notification = null)
    {
        return $this->createForm(new NotificationType(), $notification);
    }
}