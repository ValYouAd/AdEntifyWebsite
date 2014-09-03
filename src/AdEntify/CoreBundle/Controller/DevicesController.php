<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 03/09/2014
 * Time: 11:22
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\Action;
use AdEntify\CoreBundle\Entity\Device;
use AdEntify\CoreBundle\Entity\Notification;
use AdEntify\CoreBundle\Form\CommentType;
use AdEntify\CoreBundle\Form\DeviceType;
use Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection;

use AdEntify\CoreBundle\Entity\Comment;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class DevicesController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("Device")
 */
class DevicesController extends FosRestController
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="POST a Device",
     *  input="AdEntify\CoreBundle\Form\DeviceType",
     *  output="AdEntify\CoreBundle\Entity\Device",
     *  section="Device"
     * )
     *
     * @View()
     */
    public function postAction(Request $request) {
        if ($this->getUser()) {
            $em = $this->getDoctrine()->getManager();

            $device = new Device();
            $form = $this->getForm($device);
            $form->bind($request);
            if ($form->isValid()) {
                $existingDevice = $em->getRepository('AdEntifyCoreBundle:Device')->findOneBy(array(
                   'token' => $device->getToken()
                ));
                if ($existingDevice) {
                    // Update device
                    $existingDevice->fillFromExisting($device, $this->getUser());
                    $em->merge($existingDevice);
                } else {
                    // Create a new one
                    $device->setOwner($this->getUser());
                    $em->persist($device);
                }

                $em->flush();

                return $device;
            } else
                return $form;
        } else
            throw new HttpException(401);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="DELETE a device",
     *  section="Device"
     * )
     *
     * @View()
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function deleteAction($token) {
        if ($this->getUser()) {
            if (empty($token))
                throw new HttpException(404, 'Device not found');

            $em = $this->getDoctrine()->getManager();

            $device = $em->getRepository('AdEntifyCoreBundle:Device')->findOneBy(array(
                'token' => $token
            ));
            if ($device) {
                if ($device->getOwner()->getId() == $this->getUser()->getId()) {
                    $em->remove($device);
                    $em->flush();
                    return array(
                        'deleted' => true
                    );
                } else
                    throw new HttpException(403, 'You are not authorized to delete this comment');
            } else
                throw new HttpException(404, 'Device not found');
        } else
            throw new HttpException(401);
    }

    /**
     * Get form for Device
     *
     * @param null $device
     * @return mixed
     */
    protected function getForm($device = null)
    {
        return $this->createForm(new DeviceType(), $device);
    }
} 