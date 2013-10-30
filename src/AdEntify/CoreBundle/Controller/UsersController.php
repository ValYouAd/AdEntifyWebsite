<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 12/06/2013
 * Time: 15:05
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\Action;
use AdEntify\CoreBundle\Entity\Notification;
use AdEntify\CoreBundle\Entity\Photo;
use AdEntify\CoreBundle\Entity\Tag;
use AdEntify\CoreBundle\Form\VenueType;
use AdEntify\CoreBundle\Util\UserCacheManager;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @QueryParam(name="limit", requirements="\d+", default="30")
     */
    public function getPhotosAction($id, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        // Get friends list (id) array
        $facebookFriendsIds = UserCacheManager::getInstance()->getUserObject($user, UserCacheManager::USER_CACHE_KEY_FB_FRIENDS);
        if (!$facebookFriendsIds) {
            $facebookFriendsIds = $em->getRepository('AdEntifyCoreBundle:User')->refreshFriends($user, $this->container->get('fos_facebook.api'));
            UserCacheManager::getInstance()->setUserObject($user, UserCacheManager::USER_CACHE_KEY_FB_FRIENDS, $facebookFriendsIds, UserCacheManager::USER_CACHE_TTL_FB_FRIENDS);
        }

        // Get followings ids
        $followings = UserCacheManager::getInstance()->getUserObject($user, UserCacheManager::USER_CACHE_KEY_FOLLOWINGS);
        if (!$followings) {
            $followings = $user->getFollowingsIds();
            UserCacheManager::getInstance()->setUserObject($user, UserCacheManager::USER_CACHE_KEY_FOLLOWINGS, $followings, UserCacheManager::USER_CACHE_TTL_FOLLOWING);
        }

        $query = $em->createQuery('SELECT photo FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.owner owner
                WHERE photo.owner = :userId AND photo.status = :status AND photo.deletedAt IS NULL
                AND (photo.owner = :currentUserId OR photo.visibilityScope = :visibilityScope OR (owner.facebookId IS NOT NULL
                AND owner.facebookId IN (:facebookFriendsIds)) OR owner.id IN (:followings))
                ORDER BY photo.createdAt DESC')
            ->setParameters(array(
                ':userId' => $id,
                ':status' => Photo::STATUS_READY,
                ':visibilityScope' => Photo::SCOPE_PUBLIC,
                ':facebookFriendsIds' => $facebookFriendsIds,
                ':followings' => $followings,
                ':currentUserId' => $user->getId()
            ))
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $count = count($paginator);

        $photos = null;
        $pagination = null;
        if ($count > 0) {
            $photos = array();
            foreach($paginator as $photo) {
                $photos[] = $photo;
            }

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
     * @QueryParam(name="query")
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="10")
     * @View()
     */
    public function getSearchAction($query, $page = 1, $limit = 10)
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT u FROM AdEntify\CoreBundle\Entity\User u
            WHERE u.firstname LIKE :query OR u.lastname LIKE :query')
            ->setParameters(array(
                ':query' => '%'.$query.'%'
            ))
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new Paginator($query, $fetchJoinCollection = false);
        $count = count($paginator);

        $results = null;
        $pagination = null;
        if ($count > 0) {
            $results = array();
            foreach($paginator as $item) {
                $results[] = $item;
            }

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_user_search', array(
                'query' => $query
            ));
        }

        return PaginationTools::getPaginationArray($results, $pagination);
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
        $facebookFriendsIds = UserCacheManager::getInstance()->getUserObject($user, UserCacheManager::USER_CACHE_KEY_FB_FRIENDS);
        if (!$facebookFriendsIds) {
            $facebookFriendsIds = $em->getRepository('AdEntifyCoreBundle:User')->refreshFriends($user, $this->container->get('fos_facebook.api'));
            UserCacheManager::getInstance()->setUserObject($user, UserCacheManager::USER_CACHE_KEY_FB_FRIENDS, $facebookFriendsIds, UserCacheManager::USER_CACHE_TTL_FB_FRIENDS);
        }

        // Get followings ids
        $followings = UserCacheManager::getInstance()->getUserObject($user, UserCacheManager::USER_CACHE_KEY_FOLLOWINGS);
        if (!$followings) {
            $followings = $user->getFollowingsIds();
            UserCacheManager::getInstance()->setUserObject($user, UserCacheManager::USER_CACHE_KEY_FOLLOWINGS, $followings, UserCacheManager::USER_CACHE_TTL_FOLLOWING);
        }

        $query = $em->createQuery('SELECT photo, tag FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.tags tag LEFT JOIN photo.owner owner
                WHERE photo.owner != :userId AND photo.status = :status AND photo.deletedAt IS NULL AND photo.tagsCount > 0 AND tag.visible = true
                AND tag.deletedAt IS NULL AND tag.censored = FALSE AND tag.waitingValidation = FALSE
                AND (tag.validationStatus = :none OR tag.validationStatus = :granted)
                AND (photo.visibilityScope = :visibilityScope OR (owner.facebookId IS NOT NULL
                AND owner.facebookId IN (:facebookFriendsIds)) OR owner.id IN (:followings))
                ORDER BY photo.createdAt DESC')
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
            ->setMaxResults($limit);

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $count = count($paginator);

        $photos = null;
        $pagination = null;
        if ($count > 0) {
            $photos = array();
            foreach($paginator as $photo) {
                $photos[] = $photo;
            }

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
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $em = $this->getDoctrine()->getManager();

            $follower = $this->container->get('security.context')->getToken()->getUser();
            $following = $this->getAction($id);
            if ($following && $follower->getId() != $following->getId() && !$this->getIsFollowingAction($id)) {
                // FOLLOW Action & notification
                $em->getRepository('AdEntifyCoreBundle:Action')->createAction(Action::TYPE_USER_FOLLOW,
                    $follower, $following, null, Action::VISIBILITY_FRIENDS, null,
                    null, true, 'followUser');

                $follower->addFollowing($following);
                $follower->setFollowingsCount($follower->getFollowingsCount() + 1);
                $following->setFollowersCount($following->getFollowersCount() + 1);
                $em->merge($follower);
                $em->merge($following);
                $em->flush();
                return $follower;
            } else {
                $follower->removeFollowing($following);
                $follower->setFollowingsCount($follower->getFollowingsCount() - 1);
                $following->setFollowersCount($following->getFollowersCount() - 1);
                $em->merge($follower);
                $em->merge($following);
                $em->flush();
            }
        }
    }

    /**
     * @View()
     *
     * @param $id
     */
    public function getIsFollowingAction($id)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $follower = $this->container->get('security.context')->getToken()->getUser();

            return $this->getDoctrine()->getManager()->createQuery('SELECT COUNT(u.id) FROM AdEntify\CoreBundle\Entity\User u
                LEFT JOIN u.followings following WHERE u.id = :userId AND following.id = :followingId')
                ->setParameters(array(
                    'userId' => $follower->getId(),
                    'followingId' => $id
                ))
                ->getSingleScalarResult() > 0 ? true : false;
        }
        throw new HttpException(403);
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
                'createdAt' => 'DESC'
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
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="10")
     *
     * @View()
     */
    public function getHashtagsAction($id, $page = 1, $limit = 10)
    {
        $query = $this->getDoctrine()->getManager()->createQuery('SELECT hashtag FROM AdEntify\CoreBundle\Entity\Hashtag hashtag
            LEFT JOIN hashtag.photos photo LEFT JOIN photo.owner owner
            WHERE owner.id = :userId')
        ->setParameters(array(
                'userId'=> $id
            ))
        ->setFirstResult(($page - 1) * $limit)
        ->setMaxResults($limit);

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $count = count($paginator);

        $hashtags = null;
        $pagination = null;
        if ($count > 0) {
            $hashtags = array();
            foreach ($paginator as $hashtag)
                $hashtags[] = $hashtag;

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this,
                'api_v1_get_user_hashtags', array(
                    'id' => $id
                ));
        }
        return PaginationTools::getPaginationArray($hashtags, $pagination);
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
                'owner' => $user->getId()
            ), array(
                'createdAt' => 'DESC'
            ), 10);
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
                $changePasswordService = $request->request->get('fos_user_change_password');
                $user->setPlainPassword($changePasswordService['new']);
                $this->container->get('fos_user.user_manager')->updateUser($user);
                return $user;
            } else {
                return $form->getErrorsAsString();
            }
        } else
            throw new HttpException(403, 'Forbidden');
    }

    /**
     * @View()
     */
    public function getFollowingsAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AdEntifyCoreBundle:User')->find($id);
        if ($user) {
            return $this->getDoctrine()->getManager()->createQuery('SELECT user FROM AdEntify\CoreBundle\Entity\User user
            LEFT JOIN user.followings following WHERE following.id = :userId')
                ->setParameters(array(
                    'userId' => $user->getId()
                ))
                ->setMaxResults(10)
                ->getResult();
        } else {
            throw new NotFoundHttpException('User not found');
        }
    }

    /**
     * @View()
     */
    public function getFollowersAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AdEntifyCoreBundle:User')->find($id);
        if ($user) {
            return $this->getDoctrine()->getManager()->createQuery('SELECT user FROM AdEntify\CoreBundle\Entity\User user
            LEFT JOIN user.followers follower WHERE follower.id = :userId')
                ->setParameters(array(
                    'userId' => $user->getId()
                ))
                ->setMaxResults(10)
                ->getResult();
        } else {
            throw new NotFoundHttpException('User not found');
        }
    }
}