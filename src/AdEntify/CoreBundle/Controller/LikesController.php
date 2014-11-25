<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 18/04/2013
 * Time: 18:45
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\Action;
use AdEntify\CoreBundle\Entity\Notification;
use AdEntify\CoreBundle\Entity\Photo;
use Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection;

use AdEntify\CoreBundle\Entity\Like;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class LikesController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("Like")
 */
class LikesController extends FosRestController
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Post a photo like, return true if liked, false on unliked",
     *  output="bool",
     *  section="Like",
     *  statusCodes={
     *         200="Returned when successful",
     *         401="Returned when authentication is required",
     *     }
     * )
     *
     * @View(serializerGroups={"details"})
     */
    public function postAction(Request $request)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            if ($request->request->has('photoId') && is_numeric($request->request->get('photoId'))) {
                $em = $this->getDoctrine()->getManager();
                $user = $this->getUser();

                $photo = $em->getRepository('AdEntifyCoreBundle:Photo')->find($request->request->get('photoId'));
                if (!$photo)
                    throw new HttpException(404);

                $like = $em->createQuery('SELECT l FROM AdEntify\CoreBundle\Entity\Like l
                    LEFT JOIN l.photo p WHERE l.liker = :userId AND p.id = :photoId')
                    ->setParameters(array(
                        ':photoId' => $request->request->get('photoId'),
                        ':userId' => $user->getId()
                    ))
                    ->setMaxResults(1)
                    ->getOneOrNullResult();

                if (!$like || $like->getDeletedAt()) {
                    // Create the like
                    if (!$like) {
                        $like = new Like();
                        $like->setIpAddress($request->getClientIp())->setPhoto($photo)->setLiker($user);
                        $em->persist($like);
                    } else {
                        $like->setDeletedAt(null);
                        $em->merge($photo);
                        $em->merge($like);
                    }

                    $sendNotification = $user->getId() != $photo->getOwner()->getId();
                    $em->getRepository('AdEntifyCoreBundle:Action')->createAction(Action::TYPE_PHOTO_LIKE,
                        $user, $photo->getOwner(), array($photo), Action::getVisibilityWithPhotoVisibility($photo->getVisibilityScope()), $photo->getId(),
                        $em->getClassMetadata(get_class($photo))->getName(), $sendNotification, $user ? 'memberLikedPhoto': 'anonymousLikedPhoto');

                    $em->flush();

                    if ($this->getUser()->getId() != $like->getPhoto()->getOwner()->getId()) {
                        $pushNotificationService = $this->get('ad_entify_core.pushNotifications');
                        $options = $pushNotificationService->getOptions('pushNotification.photoLike', array(
                            '%user%' => $user->getFullname()
                        ), array(
                            'photoId' => $like->getPhoto()->getId()
                        ));
                        $pushNotificationService->sendToUser($like->getPhoto()->getOwner(), $options);
                    }

                    return array(
                        'liked' => true
                    );
                } else {
                    $like->setDeletedAt(new \DateTime());
                    $em->merge($photo);
                    $em->merge($like);
                    $em->flush();
                    return array(
                        'liked' => false
                    );
                }
            }
        } else {
            throw new HttpException(401);
        }
    }
}