<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 12/06/2013
 * Time: 15:05
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\Notification;
use AdEntify\CoreBundle\Entity\Photo;
use AdEntify\CoreBundle\Entity\Tag;
use AdEntify\CoreBundle\Form\VenueType;
use FOS\UserBundle\Form\Model\ChangePassword;
use FOS\UserBundle\Form\Type\ChangePasswordFormType;
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
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class UsersController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("User")
 */
class UsersController extends FosRestController
{
    /**
     * @View()
     *
     * @return User
     */
    public function getAction($id)
    {
        return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:User')->find($id);
    }

    /**
     * @param $id
     *
     * @View()
     *
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="20")
     */
    public function getPhotosAction($id, $page = 1, $limit = 20)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        // Get friends list (id) array
        $facebookFriendsIds = $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:User')
            ->refreshFriends($user, $this->container->get('fos_facebook.api'));

        // Get followings ids
        $followings = $user->getFollowingsIds();

        $count = $em->createQuery('SELECT count(photo.id) FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.owner owner
                WHERE photo.owner = :userId AND photo.status = :status AND (photo.visibilityScope = :visibilityScope
                OR (owner.facebookId IS NOT NULL AND owner.facebookId IN (:facebookFriendsIds)) OR owner.id IN (:followings))')
            ->setParameters(array(
                ':userId' => $id,
                ':status' => Photo::STATUS_READY,
                ':visibilityScope' => Photo::SCOPE_PUBLIC,
                ':facebookFriendsIds' => $facebookFriendsIds,
                ':followings' => $followings
            ))
            ->getSingleScalarResult();

        $photos = null;
        $pagination = null;
        if ($count > 0) {
            $photos = $em->createQuery('SELECT photo, tag FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.tags tag LEFT JOIN photo.owner owner
                WHERE photo.owner = :userId AND photo.status = :status
                AND (photo.visibilityScope = :visibilityScope OR (owner.facebookId IS NOT NULL
                AND owner.facebookId IN (:facebookFriendsIds)) OR owner.id IN (:followings))
                ORDER BY photo.created_at DESC')
                ->setParameters(array(
                    ':userId' => $id,
                    ':status' => Photo::STATUS_READY,
                    ':visibilityScope' => Photo::SCOPE_PUBLIC,
                    ':facebookFriendsIds' => $facebookFriendsIds,
                    ':followings' => $followings
                ))
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getResult();

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_user_photos', array(
                'id' => $id
            ));
        }

        return PaginationTools::getPaginationArray($photos, $pagination);
    }

    /**
     * @View()
     */
    public function getFavoritesAction()
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        return $user->getFavoritePhotos();
    }

    /**
     * @param $query
     * @param int $limit
     *
     * @QueryParam(name="limit", default="10")
     * @View()
     */
    public function getSearchAction($query, $limit)
    {
        return $this->getDoctrine()->getManager()->createQuery('SELECT user FROM AdEntify\CoreBundle\Entity\User user
            WHERE user.firstname LIKE :query OR user.lastname LIKE :query')
            ->setParameter(':query', '%'.$query.'%')
            ->setMaxResults($limit)
            ->getResult();
    }

    /**
     * @View()
     *
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="20")
     *
     * @return ArrayCollection|null
     */
    public function getFeedAction($page = 1, $limit = 20)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        // Get friends list (id) array
        $facebookFriendsIds = $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:User')->refreshFriends($user, $this->container->get('fos_facebook.api'));

        // Get followings ids
        $followings = $user->getFollowingsIds();

        $count = $em->createQuery('SELECT count(photo.id) FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.owner owner
                WHERE photo.owner != :userId AND photo.status = :status AND photo.tagsCount > 0
                AND (photo.visibilityScope = :visibilityScope OR (owner.facebookId IS NOT NULL
                AND owner.facebookId IN (:facebookFriendsIds)) OR owner.id IN (:followings))')
            ->setParameters(array(
                ':userId' => $user->getId(),
                ':status' => Photo::STATUS_READY,
                ':visibilityScope' => Photo::SCOPE_PUBLIC,
                ':facebookFriendsIds' => $facebookFriendsIds,
                ':followings' => $followings
            ))
            ->getSingleScalarResult();

        $photos = null;
        $pagination = null;
        if ($count > 0) {
            $photos = $em->createQuery('SELECT photo, tag FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.tags tag LEFT JOIN photo.owner owner
                WHERE photo.owner != :userId AND photo.status = :status AND photo.tagsCount > 0 AND tag.visible = true
                AND tag.deleted_at IS NULL AND tag.censored = FALSE AND tag.waitingValidation = FALSE
                AND (tag.validationStatus = :none OR tag.validationStatus = :granted)
                AND (photo.visibilityScope = :visibilityScope OR (owner.facebookId IS NOT NULL
                AND owner.facebookId IN (:facebookFriendsIds)) OR owner.id IN (:followings))
                ORDER BY photo.created_at DESC')
                ->setParameters(array(
                    ':userId' => $user->getId(),
                    ':status' => Photo::STATUS_READY,
                    ':visibilityScope' => Photo::SCOPE_PUBLIC,
                    ':facebookFriendsIds' => $facebookFriendsIds,
                    ':followings' => $followings,
                    ':none' => Tag::VALIDATION_NONE,
                    ':granted' => Tag::VALIDATION_GRANTED
                ))
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getResult();

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_user_feed');
        }

        return PaginationTools::getPaginationArray($photos, $pagination);
    }

    /**
     * @param $id following id
     * @return mixed
     *
     * @View()
     */
    public function postFollowerAction($id)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
            $follower = $this->container->get('security.context')->getToken()->getUser();
            $following = $this->getAction($id);
            if ($following && $follower->getId() != $following->getId()) {
                $follower->addFollower($following);
                $this->getDoctrine()->getManager()->merge($follower);
                $this->getDoctrine()->getManager()->flush();

                return $follower;
            }
        }
    }

    /**
     * @param $id
     *
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="10")
     *
     * @View()
     */
    public function getLikedPhotosAction($id, $page = 1, $limit = 10)
    {
        $em = $this->getDoctrine()->getManager();
        $count = $em->createQuery('SELECT COUNT(l.id) FROM AdEntify\CoreBundle\Entity\Like l LEFT JOIN l.liker as liker
            WHERE liker.id = :id')
            ->setParameter('id', $id)
            ->getSingleScalarResult();

        $photos = null;
        $pagination = null;

        if ($count > 0) {
            $likes = $em->getRepository('AdEntifyCoreBundle:Like')->findBy(array(
                'liker' => $id
            ), array(
                'created_at' => 'DESC'
            ), $limit, ($page - 1) * $limit);
            $photos = array();
            foreach ($likes as $like) {
                $photos[] = $like->getPhoto();
            }

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this,
                'api_v1_get_user_liked_photos', array(
                    'id' => $id
                ));

        }

        return PaginationTools::getPaginationArray($photos, $pagination);
    }

    /**
     * @param $id
     *
     * @View()
     */
    public function getNotificationsAction($id)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        if ($user->getId() == $id) {
            return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Notification')->findBy(array(
                'status' => Notification::STATUS_UNREAD,
                'owner' => $user->getId()
            ));
        } else
            throw new HttpException(403, 'Forbidden');
    }

    /**
     * @View()
     *
     * @param $id
     */
    public function postChangePasswordAction($id, Request $request)
    {
        $user = $this->container->get('security.context')->getToken()->getUser();
        if ($user->getId() == $id) {
            $model = new ChangePassword();
            $form = $this->createForm(new ChangePasswordFormType(), $model);
            $form->setData($model);
            $form->bind($request);
            if ($form->isValid()) {
                $user->setPlainPassword($request->request->get('fos_user_change_password')['new']);
                $this->container->get('fos_user.user_manager')->updateUser($user);
                return $user;
            } else {
                return $form->getErrorsAsString();
            }
        } else
            throw new HttpException(403, 'Forbidden');
    }
}