<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 02/08/2013
 * Time: 15:22
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\User;
use AdEntify\CoreBundle\Model\Thumb;
use AdEntify\CoreBundle\Util\FileTools;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

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
     * @ApiDoc(
     *  resource=true,
     *  description="Get services status for current logged user",
     *  statusCodes={
     *      200="Returned the services linked or not of the current logged user",
     *      401="Returned when authentication is required",
     *  },
     *  section="Settings"
     * )
     *
     * @View(serializerGroups={"details"})
     */
    public function getUserServicesAction()
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $em = $this->getDoctrine()->getManager();
            $securityContext = $this->container->get('security.context');
            $user = $securityContext->getToken()->getUser();

            $services = $em->getRepository('AdEntifyCoreBundle:OAuthUserInfo')->findBy(array(
                'user' => $user->getId()
            ));

            $availableServices = array(
                'instagram',
                'Facebook',
                'Flickr',
            );

            $connectedServices = array();
            if ($services) {
                foreach($services as $service) {
                    $connectedServices[] = array(
                        'id' => $service->getId(),
                        'service_name' => $service->getServiceName(),
                        'linked' => true
                    );
                }
            }
            if ($securityContext->isGranted('ROLE_FACEBOOK')) {
                $connectedServices[] = array(
                    'service_name' => 'Facebook',
                    'cant_delete' => true,
                    'linked' => true
                );
            }

            foreach($connectedServices as $connectedService) {
                if(($key = array_search($connectedService['service_name'], $availableServices)) !== false) {
                    unset($availableServices[$key]);
                }
            }
            foreach($availableServices as $service) {
                $connectedServices[] = array(
                    'service_name' => $service,
                    'linked' => false
                );
            }

            return $connectedServices;
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Post user settings",
     *  output="AdEntify\CoreBundle\Entity\User",
     *  statusCodes={
     *      200="Returned if successfull",
     *      401="Returned when authentication is required",
     *  },
     *  section="Settings"
     * )
     *
     * @View(serializerGroups={"me"})
     */
    public function postSettingsAction() {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $securityContext->getToken()->getUser();

            $user->setShareDataWithAdvertisers($this->getRequest()->request->has('shareDataAdvertisers'));
            $user->setPartnersNewsletters($this->getRequest()->request->has('partnersNewsletters'));

            if ($this->getRequest()->request->has('birthday'))
                $user->setBirthday(new \DateTime($this->getRequest()->request->get('birthday')));

            if (isset($_FILES['profilepicture'])) {
                $uploadedFile = $_FILES['profilepicture'];
                $user = $this->container->get('security.context')->getToken()->getUser();
                $path = FileTools::getUserProfilePicturePath($user);
                $filename = uniqid().$uploadedFile['name'][0];
                $url = $this->container->get('adentify_storage.file_manager')->upload($this->getRequest()->files->get('profilepicture'), $path, $filename);
                if ($url) {
                    $thumb = new Thumb();
                    $thumb->setOriginalPath($url);
                    $thumb->addThumbSize(FileTools::PROFILE_PICTURE_TYPE);

                    $thumbs = $this->container->get('ad_entify_core.thumb')->generateProfilePictureThumb($thumb, $user, $filename);
                    $thumbs['original'] = $url;

                    $user->setProfilePicture($thumbs['profile-picture']['filename']);
                } else {
                    throw new HttpException(500);
                }
            }

            $this->getDoctrine()->getManager()->merge($user);
            $this->getDoctrine()->getManager()->flush();

            return $user;
        } else {
            throw new HttpException(401);
        }
    }
}