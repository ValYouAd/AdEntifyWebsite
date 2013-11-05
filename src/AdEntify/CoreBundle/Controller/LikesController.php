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
     * @View()
     */
    public function postAction(Request $request)
    {
        if ($request->request->has('photoId') && is_numeric($request->request->get('photoId'))) {
            $em = $this->getDoctrine()->getManager();
            $securityContext = $this->container->get('security.context');
            $user = $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ? $this->container->get('security.context')->getToken()->getUser() : 0;
            $like = $em->createQuery('SELECT l FROM AdEntify\CoreBundle\Entity\Like l
              LEFT JOIN l.photo p WHERE (l.ipAddress = :ipAddress OR l.liker = :userId) AND p.id = :photoId')
                ->setParameters(array(
                    ':ipAddress' => $request->getClientIp(),
                    ':photoId' => $request->request->get('photoId'),
                    ':userId' => $user ? $user->getId() : $user
                ))
                ->SetMaxResults(1)
                ->getOneOrNullResult();

            if (!$like) {
                $photo = $em->getRepository('AdEntifyCoreBundle:Photo')->find($request->request->get('photoId'));
                if ($photo) {
                    // Create the like
                    $like = new Like();
                    $like->setIpAddress($request->getClientIp())->setPhoto($photo);

                    $currentUser = null;
                    if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED'))
                        $currentUser = $securityContext->getToken()->getUser();

                    $sendNotification = $user->getId() != $photo->getOwner()->getId();
                    $em->getRepository('AdEntifyCoreBundle:Action')->createAction(Action::TYPE_PHOTO_LIKE,
                        $currentUser, $photo->getOwner(), array($photo), Action::VISIBILITY_FRIENDS, $photo->getId(),
                        get_class($photo), $sendNotification, $currentUser ? 'memberLikedPhoto': 'anonymousLikedPhoto');

                    if ($currentUser)
                        $like->setLiker($currentUser);

                    $em->persist($like);
                    $em->flush();

                    return true;
                }
            } else {
                if ($user && $like) {
                    $em->remove($like);
                    $em->flush();
                    return false;
                }
            }
        }
    }
}