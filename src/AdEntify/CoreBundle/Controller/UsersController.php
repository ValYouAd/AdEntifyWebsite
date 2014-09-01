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
use AdEntify\CoreBundle\Entity\Reward;
use AdEntify\CoreBundle\Entity\Tag;
use AdEntify\CoreBundle\Entity\TagIncome;
use AdEntify\CoreBundle\Entity\TagPoint;
use AdEntify\CoreBundle\Entity\Task;
use AdEntify\CoreBundle\Form\VenueType;
use AdEntify\CoreBundle\Util\UserCacheManager;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Tools\Pagination\Paginator;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Type\ChangePasswordFormType;
use FOS\UserBundle\FOSUserEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
use JMS\SecurityExtraBundle\Annotation\Secure;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\UserBundle\Model\UserInterface;

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
     * @ApiDoc(
     *  resource=true,
     *  description="Get a user by ID",
     *  output="AdEntify\CoreBundle\Entity\User",
     *  section="User"
     * )
     *
     * @View(serializerGroups={"details"})
     *
     * @return User
     */
    public function getAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $user = $em->getRepository('AdEntifyCoreBundle:User')->find($id);
        if ($user) {
            $lastPhoto = $em->createQuery('SELECT photo
                                           FROM AdEntifyCoreBundle:Photo photo
                                           WHERE photo.owner = :userId AND photo.status = :status AND photo.deletedAt IS NULL AND photo.visibilityScope = :visibilityScope
                                           ORDER BY photo.createdAt DESC')
                ->setParameters(array(
                    ':status' => Photo::STATUS_READY,
                    ':visibilityScope' => Photo::SCOPE_PUBLIC,
                    ':userId' => $user->getId(),
                ))
                ->setMaxResults(1)
                ->getOneOrNullResult();

            $user->setLastPhoto($lastPhoto);

            $count = $em->createQuery('SELECT COUNT(DISTINCT photo)
                                       FROM AdEntifyCoreBundle:Photo photo
                                       WHERE photo.owner = :userId AND photo.status = :status AND photo.deletedAt IS NULL AND photo.visibilityScope = :visibilityScope
                                       ORDER BY photo.createdAt DESC')
                ->setParameters(array(
                    ':status' => Photo::STATUS_READY,
                    ':visibilityScope' => Photo::SCOPE_PUBLIC,
                    ':userId' => $user->getId(),
                ))
                ->getSingleScalarResult();

            $randomPhoto = $em->createQuery('SELECT DISTINCT photo
                                             FROM AdEntifyCoreBundle:Photo photo
                                             WHERE photo.owner = :userId AND photo.status = :status AND photo.deletedAt IS NULL AND photo.visibilityScope = :visibilityScope
                                             ORDER BY photo.createdAt DESC')
                ->setParameters(array(
                    ':status' => Photo::STATUS_READY,
                    ':visibilityScope' => Photo::SCOPE_PUBLIC,
                    ':userId' => $user->getId(),
                ))
                ->setFirstResult(rand(0, $count - 1))
                ->setMaxResults(1)
                ->getOneOrNullResult();

            $user->setRandomPhoto($randomPhoto);
            return $user;
        } else
            throw new HttpException(404);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get current logged in user",
     *  output="AdEntify\CoreBundle\Entity\User",
     *  section="User"
     * )
     *
     * @View(serializerGroups={"me"})
     *
     * @return User
     */
    public function getCurrentAction()
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return $securityContext->getToken()->getUser();
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get user photos",
     *  output="AdEntify\CoreBundle\Entity\Photo",
     *  section="User"
     * )
     *
     * @param $id
     *
     * @View(serializerGroups={"list"})
     *
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="30")
     */
    public function getPhotosAction($id, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        $user = null;
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $user = $this->container->get('security.context')->getToken()->getUser();
        }

        // Get friends list (id) array
        $facebookFriendsIds = array(0);
        $followings = array(0);
        if ($user) {
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
                ':currentUserId' => $user ? $user->getId() : 0
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
     * @ApiDoc(
     *  resource=true,
     *  description="Get user favorites photos",
     *  output="AdEntify\CoreBundle\Entity\Photo",
     *  section="User"
     * )
     *
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="30")
     *
     * @View(serializerGroups={"list"})
     */
    public function getFavoritesAction($page, $limit)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $securityContext->getToken()->getUser();

            $query = $this->getDoctrine()->getManager()->createQuery('SELECT photo, tag FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.favoritesUsers u LEFT JOIN photo.tags tag WITH (tag.visible = true AND tag.deletedAt IS NULL
                      AND tag.censored = false AND tag.validationStatus != :denied)
                WHERE u.id = :userId AND photo.status = :status AND photo.deletedAt IS NULL
                ORDER BY photo.createdAt DESC')
                ->setParameters(array(
                    ':userId' => $user->getId(),
                    ':status' => Photo::STATUS_READY,
                    ':denied' => Tag::VALIDATION_DENIED
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

                $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_user_favorites');
            }

            return PaginationTools::getPaginationArray($photos, $pagination);
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Search users",
     *  output="AdEntify\CoreBundle\Entity\User",
     *  section="User"
     * )
     *
     * @param $query
     * @param int $limit
     *
     * @QueryParam(name="query")
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="10")
     * @View(serializerGroups={"list"})
     */
    public function getSearchAction($query, $page = 1, $limit = 10)
    {
        $em = $this->getDoctrine()->getManager();
        $securityContext = $this->container->get('security.context');

        $query = $em->createQuery('SELECT user, (SELECT COUNT(u.id) FROM AdEntifyCoreBundle:User u
                LEFT JOIN u.followings following WHERE u.id = :currentUserId AND following.id = user.id) as followed FROM AdEntify\CoreBundle\Entity\User user
                WHERE user.firstname LIKE :query OR user.lastname LIKE :query')
            ->setParameters(array(
                ':query' => '%'.$query.'%',
                'currentUserId' => $securityContext->isGranted('IS_AUTHENTICATED_FULLY') ? $this->container->get('security.context')->getToken()->getUser()->getId() : 0
            ))
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new Paginator($query, $fetchJoinCollection = false);
        $count = count($paginator);

        $results = null;
        $pagination = null;
        if ($count > 0) {
            $results = array();
	        foreach ($paginator as $entry) {
                $user = $entry[0];
                $user->setFollowed($entry['followed'] > 0 ? true : false);
                $results[] = $user;
            }

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_user_search', array(
                'query' => $query
            ));
        }

        return PaginationTools::getPaginationArray($results, $pagination);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="POST a new follower for a user",
     *  section="User"
     * )
     *
     * @param $id following id
     * @return mixed
     *
     * @View(serializerGroups={"details"})
     */
    public function postFollowerAction($id)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $em = $this->getDoctrine()->getManager();

            $follower = $this->container->get('security.context')->getToken()->getUser();
            $following = $this->getAction($id);

            $isFollowed = $this->getIsFollowedAction($id);
            if ($following && $follower->getId() != $following->getId() && !$isFollowed['followed']) {
                // FOLLOW Action & notification
                $em->getRepository('AdEntifyCoreBundle:Action')->createAction(Action::TYPE_USER_FOLLOW,
                    $follower, $following, null, Action::VISIBILITY_PUBLIC, $following->getId(),
                    $em->getClassMetadata(get_class($following))->getName(), true, 'followUser');

                $follower->addFollowing($following);
                $follower->setFollowingsCount($follower->getFollowingsCount() + 1);
                $following->setFollowersCount($following->getFollowersCount() + 1);
                $em->merge($follower);
                $em->merge($following);
                $em->flush();

                // Empty followings cache
                UserCacheManager::getInstance()->deleteUserObject($follower, UserCacheManager::USER_CACHE_KEY_FOLLOWINGS);

                return array(
                    'followed' => true
                );
            } else {
                $follower->removeFollowing($following);
                $follower->setFollowingsCount($follower->getFollowingsCount() - 1);
                $following->setFollowersCount($following->getFollowersCount() - 1);
                $em->merge($follower);
                $em->merge($following);
                $em->flush();

                // Empty followings cache
                UserCacheManager::getInstance()->deleteUserObject($follower, UserCacheManager::USER_CACHE_KEY_FOLLOWINGS);

                return array(
                    'followed' => false,
                );
            }
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Check if current logged user follow the user id in parameter",
     *  output="bool",
     *  section="User",
     * parameters={
     *   {"name"="id", "dataType"="integer", "required"=true, "description"="user ID"}
     * }
     * )
     *
     * @View(serializerGroups={"details"})
     *
     * @param $id
     */
    public function getIsFollowedAction($id)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $follower = $this->container->get('security.context')->getToken()->getUser();

            $followed = $this->getDoctrine()->getManager()->createQuery('SELECT COUNT(u.id) FROM AdEntify\CoreBundle\Entity\User u
                LEFT JOIN u.followings following WHERE u.id = :userId AND following.id = :followingId')
                ->setParameters(array(
                    'userId' => $follower->getId(),
                    'followingId' => $id
                ))
                ->getSingleScalarResult() > 0;

            return array(
                'followed' => $followed
            );
        } else
            throw new HttpException(401);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="GET user liked photos with his ID",
     *  output="AdEntify\CoreBundle\Entity\Photo",
     *  section="User",
     * parameters={
     *   {"name"="id", "dataType"="integer", "required"=true, "description"="user ID"}
     * }
     * )
     *
     * @param $id
     *
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="10")
     *
     * @View(serializerGroups={"details"})
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
     * @ApiDoc(
     *  resource=true,
     *  description="GET user hashtags with user ID",
     *  output="AdEntify\CoreBundle\Entity\Hashtag",
     *  section="User",
     * parameters={
     *   {"name"="id", "dataType"="integer", "required"=true, "description"="user ID"}
     * }
     * )
     *
     * @param $id
     *
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="10")
     *
     * @View(serializerGroups={"details"})
     */
    public function getHashtagsAction($id, $page = 1, $limit = 10)
    {
        $query = $this->getDoctrine()->getManager()->createQuery('SELECT hashtag FROM AdEntify\CoreBundle\Entity\Hashtag hashtag
            LEFT JOIN hashtag.photos photo LEFT JOIN photo.owner owner
            WHERE owner.id = :userId AND photo.deletedAt IS NULL ORDER BY photo.createdAt DESC')
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
     * @ApiDoc(
     *  resource=true,
     *  description="GET notifications of current logged in user",
     *  output="AdEntify\CoreBundle\Entity\Notification",
     *  section="User"
     * )
     *
     * @param $id
     *
     * @View(serializerGroups={"slight-list"})
     */
    public function getNotificationsAction()
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $user = $this->container->get('security.context')->getToken()->getUser();
            return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Notification')->findBy(array(
                'owner' => $user->getId()
            ), array(
                'createdAt' => 'DESC'
            ), 10);
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="POST Change password",
     *  input="FOS\UserBundle\Form\Type\ChangePasswordFormType",
     *  output="AdEntify\CoreBundle\Entity\User",
     *  section="User",
     * parameters={
     *   {"name"="id", "dataType"="integer", "required"=true, "description"="user ID"}
     * }
     * )
     *
     * @View(serializerGroups={"details"})
     *
     * @param $id
     */
    public function postChangePasswordAction($id, Request $request)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $user = $this->container->get('security.context')->getToken()->getUser();
            if ($user->getId() == $id) {
                $form = $this->createForm(new ChangePasswordFormType('AdEntify\\CoreBundle\\Entity\\User'));
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $changePasswordService = $request->request->get('fos_user_change_password');
                    $user->setPlainPassword($changePasswordService['plainPassword']);
                    $this->container->get('fos_user.user_manager')->updateUser($user);
                    return $user;
                } else {
                    return $form->getErrorsAsString();
                }
            } else
                throw new HttpException(403);
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="POST Send mail to reset a password",
     *  output="AdEntify\CoreBundle\Entity\User",
     *  section="User"
     * )
     *
     * @View(serializerGroups={"details"})
     *
     */
    public function postResetPasswordAction(Request $request)
    {
        $username = $request->request->get('username');

        /** @var $user UserInterface */
        $user = $this->container->get('fos_user.user_manager')->findUserByUsernameOrEmail($username);

        if (null === $user) {
            throw new HttpException(404);
        }

        if ($user->isPasswordRequestNonExpired($this->container->getParameter('fos_user.resetting.token_ttl'))) {
            throw new HttpException(403);
        }

        if (null === $user->getConfirmationToken()) {
            /** @var $tokenGenerator \FOS\UserBundle\Util\TokenGeneratorInterface */
            $tokenGenerator = $this->container->get('fos_user.util.token_generator');
            $user->setConfirmationToken($tokenGenerator->generateToken());
        }

        $this->container->get('fos_user.mailer')->sendResettingEmailMessage($user);
        $user->setPasswordRequestedAt(new \DateTime());
        $this->container->get('fos_user.user_manager')->updateUser($user);

        return $user;
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="GET followings by user ID",
     *  output="AdEntify\CoreBundle\Entity\User",
     *  section="User",
     * parameters={
     *   {"name"="id", "dataType"="integer", "required"=true, "description"="user ID"}
     * }
     * )
     *
     * @View(serializerGroups={"details"})
     *
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="5")
     *
     */
    public function getFollowingsAction($id, $page = 1, $limit = 5)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AdEntifyCoreBundle:User')->find($id);
	    $securityContext = $this->container->get('security.context');
        if ($user) {
            $query = $this->getDoctrine()->getManager()->createQuery('SELECT user, (SELECT COUNT(u.id) FROM AdEntifyCoreBundle:User u
            LEFT JOIN u.followings f WHERE u.id = :currentUserId AND f.id = user.id) as followed FROM AdEntify\CoreBundle\Entity\User user
                LEFT JOIN user.followers follower WHERE follower.id = :userId')
                    ->setParameters(array(
                'userId' => $user->getId(),
                'currentUserId' => $securityContext->isGranted('IS_AUTHENTICATED_FULLY') ? $this->container->get('security.context')->getToken()->getUser()->getId() : 0
                    ))
                    ->setFirstResult(($page - 1) * $limit)
                    ->setMaxResults($limit);

                $paginator = new Paginator($query, $fetchJoinCollection = true);
                $count = count($paginator);

                $users = null;
                $pagination = null;
            if ($count > 0) {
                $users = array();
                foreach ($paginator as $entry) {
                    $user = $entry[0];
                    $user->setFollowed($entry['followed'] > 0 ? true : false);
                            $users[] = $user;
                }

                $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this,
                    'api_v1_get_user_followings', array(
                        'id' => $id
                    ));
            }
            return PaginationTools::getPaginationArray($users, $pagination);
        } else {
            throw new NotFoundHttpException('User not found');
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="GET followers by user ID",
     *  output="AdEntify\CoreBundle\Entity\User",
     *  section="User",
     * parameters={
     *   {"name"="id", "dataType"="integer", "required"=true, "description"="user ID"}
     * }
     * )
     *
     * @View(serializerGroups={"details"})
     *
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="5")
     *
     */
    public function getFollowersAction($id, $page = 1, $limit = 5)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AdEntifyCoreBundle:User')->find($id);
	    $securityContext = $this->container->get('security.context');
        if ($user) {
            $query = $this->getDoctrine()->getManager()->createQuery('SELECT user, (SELECT COUNT(u.id) FROM AdEntifyCoreBundle:User u
            LEFT JOIN u.followings f WHERE u.id = :currentUserId AND f.id = user.id) as followed FROM AdEntify\CoreBundle\Entity\User user
                LEFT JOIN user.followings following WHERE following.id = :userId')
                    ->setParameters(array(
                'userId' => $user->getId(),
                'currentUserId' => $securityContext->isGranted('IS_AUTHENTICATED_FULLY') ? $this->container->get('security.context')->getToken()->getUser()->getId() : 0
                    ))
                    ->setFirstResult(($page - 1) * $limit)
                    ->setMaxResults($limit);

                $paginator = new Paginator($query, $fetchJoinCollection = true);
                $count = count($paginator);

                $users = null;
                $pagination = null;
            if ($count > 0) {
                $users = array();
                foreach ($paginator as $entry) {
                    $user = $entry[0];
                    $user->setFollowed($entry['followed'] > 0 ? true : false);
                            $users[] = $user;
                }

                $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this,
                    'api_v1_get_user_followers', array(
                        'id' => $id
                    ));
            }
            return PaginationTools::getPaginationArray($users, $pagination);
        } else {
            throw new NotFoundHttpException('User not found');
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="GET top followers",
     *  output="AdEntify\CoreBundle\Entity\User",
     *  section="User"
     * )
     *
     * @View(serializerGroups={"slight-list"})
     */
    public function getTopFollowersAction()
    {
        return $this->getDoctrine()->getManager()->createQuery('SELECT user FROM AdEntify\CoreBundle\Entity\User user
              ORDER BY user.followersCount DESC')
            ->setMaxResults(10)
            ->getResult();
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Delete user user ID, mandatory : logged user should be the same as user ID in parameter",
     *  output="Boolean",
     *  section="User",
     * parameters={
     *   {"name"="id", "dataType"="integer", "required"=true, "description"="user ID"}
     * }
     * )
     *
     * @View(serializerGroups={"details"})
     *
     * @param $id
     */
    public function deleteAction($id)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $user = $securityContext->getToken()->getUser();
            if ($id == $user->getId()) {
                $this->getDoctrine()->getManager()->remove($user);
                $this->getDoctrine()->getManager()->flush();
                return true;
            } else {
                throw new HttpException(403);
            }
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="GET task progress for current logged user",
     *  section="User"
     * )
     *
     * @View(serializerGroups={"details"})
     *
     * @param $id
     */
    public function taskProgressAction()
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $currentUser = $this->container->get('security.context')->getToken()->getUser();
            $task = $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Task')->findOneBy(array(
                'user' => $currentUser->getId(),
                'type' => Task::TYPE_UPLOAD
            ));
            return $task ? $task->getProgress() : '';
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Update birthday for current logged user",
     *  section="User"
     * )
     *
     * @View(serializerGroups={"details"})
     */
    public function postBirthdayAction()
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $currentUser = $this->container->get('security.context')->getToken()->getUser();
            $currentUser->setBirthday(new \DateTime($this->getRequest()->request->get('birthday')));
            $this->getDoctrine()->getManager()->merge($currentUser);
            $this->getDoctrine()->getManager()->flush();
            return true;
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="GET brands for current logged user",
     *  output="AdEntify\CoreBundle\Entity\Brand",
     *  section="User"
     * )
     *
     * @QueryParam(name="id", requirements="\d+", default="0", description="user ID")
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="9")
     *
     * @View(serializerGroups={"list"})
     */
    public function getBrandsAction($id = 0, $page = 1, $limit = 9)
    {
        $securityContext = $this->container->get('security.context');
        if ($id > 0 || $securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $query = $this->getDoctrine()->getManager()->createQuery('SELECT b FROM AdEntifyCoreBundle:Brand as b
            LEFT JOIN b.followers as follower WHERE b.validated = 1 AND follower.id = :userId')
                ->setParameters(array(
                    'userId' => $id > 0 ? $id : $securityContext->getToken()->getUser()->getId()
                ))
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $paginator = new Paginator($query, $fetchJoinCollection = true);
            $count = count($paginator);

            $brands = null;
            $pagination = null;
            if ($count > 0) {
                foreach ($paginator as $brand)
                    $brands[] = $brand;

                $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this,
                    'api_v1_get_user_brands', array(
                        'id' => $id
                    ));
            }
            return PaginationTools::getPaginationArray($brands, $pagination);
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="GET analytics for current logged user",
     *  section="User"
     * )
     *
     * @View(serializerGroups={"list"})
     */
    public function getAnalyticsAction()
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $em = $this->getDoctrine()->getManager();
            $currentUser = $this->container->get('security.context')->getToken()->getUser();

            $taggedPhotos = $em->createQuery('SELECT COUNT(p.id) FROM AdEntifyCoreBundle:Photo p WHERE p.owner = :currentUserId
            AND p.tags IS NOT EMPTY AND p.deletedAt IS NULL AND p.status = :status')
                ->setParameters(array(
                    'currentUserId' => $currentUser->getId(),
                    ':status' => Photo::STATUS_READY
                ))
                ->getSingleScalarResult();

            $untaggedPhotos = $em->createQuery('SELECT COUNT(p.id) FROM AdEntifyCoreBundle:Photo p WHERE p.owner = :currentUserId
            AND p.tags IS EMPTY AND p.deletedAt IS NULL AND p.status = :status')
                ->setParameters(array(
                    'currentUserId' => $currentUser->getId(),
                    ':status' => Photo::STATUS_READY
                ))
                ->getSingleScalarResult();

            $totalPhotos = $em->createQuery('SELECT COUNT(p.id) FROM AdEntifyCoreBundle:Photo p WHERE p.owner = :currentUserId AND p.deletedAt IS NULL AND p.status = :status')
                ->setParameters(array(
                    'currentUserId' => $currentUser->getId(),
                    ':status' => Photo::STATUS_READY
                ))
                ->getSingleScalarResult();

            return array(
                'taggedPhotos' => $taggedPhotos,
                'totalPhotos' => $totalPhotos,
                'untaggedPhotos' => $untaggedPhotos
            );
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="GET points for current logged user",
     *  output="AdEntify\CoreBundle\Entity\User",
     *  section="User"
     * )
     *
     * @View(serializerGroups={"list"})
     */
    public function getPointsAction()
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->container->get('security.context')->getToken()->getUser()->getPoints();
        } else {
            throw new HttpException(401);
        }

    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="GET credits by date rage for current logged user",
     *  section="User"
     * )
     *
     * @View(serializerGroups={"list"})
     *
     * @QueryParam(name="begin")
     * @QueryParam(name="end")
     */
    public function getCreditsByDateRangeAction($begin, $end)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->container->get('security.context')->getToken()->getUser();

            $em = $this->getDoctrine()->getManager();

            // Get SQL query parameters
            $parametersPoints = array(
                'userId' => $user->getId(),
                'status' => TagPoint::STATUS_CREDITED
            );
            $parametersIncomes = array(
                'userId' => $user->getId(),
                'status' => TagIncome::STATUS_PAID
            );
            $whereClausesPoints = array();
            $whereClausesIncomes = array();

            if ($begin) {
                $parametersPoints['begin'] = new \DateTime($begin);
                $parametersIncomes['begin'] = new \DateTime($begin);
                $whereClausesPoints[] = ' AND DATE(credited_at) >= DATE(:begin) ';
                $whereClausesIncomes[] = ' AND DATE(paid_at) >= DATE(:begin) ';
            }
            if ($end) {
                $parametersPoints['end'] = new \DateTime($end);
                $parametersIncomes['end'] = new \DateTime($end);
                $whereClausesPoints[] = ' AND DATE(credited_at) <= DATE(:end) ';
                $whereClausesIncomes[] = ' AND DATE(paid_at) <= DATE(:end) ';
            }

            $rsm = new ResultSetMapping();
            $rsm->addScalarResult('points', 'points', 'integer');
            $rsm->addScalarResult('date', 'date', 'datetime');

            $sql = 'SELECT SUM(points) AS points, credited_at AS date FROM tag_points
            WHERE user_id = :userId AND status = :status ' . implode('', $whereClausesPoints) . ' GROUP BY DATE(credited_at) ORDER BY credited_at';
            $tagPoints = $em->createNativeQuery($sql, $rsm)->setParameters($parametersPoints)->getResult();

            $rsm = new ResultSetMapping();
            $rsm->addScalarResult('income', 'income', 'decimal');
            $rsm->addScalarResult('date', 'date', 'datetime');

            $sql = 'SELECT SUM(income) AS income, paid_at AS date FROM tag_incomes
            WHERE user_id = :userId AND status = :status ' . implode('', $whereClausesIncomes) . ' GROUP BY DATE(paid_at) ORDER BY paid_at';
            $tagIncomes = $em->createNativeQuery($sql, $rsm)->setParameters($parametersIncomes)->getResult();

            $credits = array();
            foreach ($tagPoints as $tagPoint) {
                $credits[] = $tagPoint;
            }

            foreach ($tagIncomes as $tagIncome) {
                $found = false;
                foreach($credits as &$credit) {
                    if ($credit['date']->format('Y-m-d') == $tagIncome['date']->format('Y-m-d')) {
                        $credit['incomes'] = $tagIncome['income'];
                        $found = true;
                        break;
                    }
                }
                if (!$found)
                    $credits[] = $tagIncome;
            }

            return $credits;
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="GET user dashboard for current logged user",
     *  section="User"
     * )
     *
     * @View(serializerGroups={"slight-list"})
     *
     * @QueryParam(name="begin")
     * @QueryParam(name="end")
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="30")
     */
    public function getDashboardAction($begin, $end, $page = 1, $limit = 30)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            return array(
                'credits' => $this->getCreditsByDateRangeAction($begin, $end),
                'analytics' => $this->getAnalyticsAction(),
                'actions' => $this->getActionsAction($page, $limit)
            );
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="GET credits for current logged user by date",
     *  section="User"
     * )
     *
     * @View(serializerGroups={"slight-list"})
     */
    public function getCreditsByDateAction($date)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->container->get('security.context')->getToken()->getUser();
            $em = $this->getDoctrine()->getManager();

            $tagPoints = $em ->createQuery('SELECT tagPoint FROM AdEntifyCoreBundle:TagPoint tagPoint
            where tagPoint.user = :userId AND tagPoint.status = :status AND DAY(tagPoint.creditedAt) = DAY(:date)')
                ->setParameters(array(
                    'userId' => $user->getId(),
                    'status' => TagPoint::STATUS_CREDITED,
                    'date' => new \DateTime($date)
                ))->getResult();

            $tagIncomes = $em->createQuery('SELECT tagIncome FROM AdEntifyCoreBundle:TagIncome tagIncome
            WHERE tagIncome = :userId AND tagIncome.status = :status AND DAY(tagIncome.paidAt) = DAY(:date)')
                ->setParameters(array(
                    'userId' => $user->getId(),
                    'status' => TagIncome::STATUS_PAID,
                    'date' => new \DateTime($date)
                ))->getResult();

            $credits = array();
            foreach ($tagPoints as $tagPoint) {
                $credits[] = $this->formatCredit($tagPoint);
            }

            foreach ($tagIncomes as $tagIncome) {
                $found = false;
                foreach($credits as &$credit) {
                    if ($credit['tagId'] == $tagIncome->getTag()->getId()) {
                        $credit['income'] = $tagIncome->getIncome();
                        $found = true;
                        break;
                    }
                }
                if (!$found)
                    $credits[] = $this->formatCredit(null, $tagIncome);
            }

            return $credits;
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="GET rewards by user ID",
     *  output="AdEntify\CoreBundle\Entity\Reward",
     *  section="User",
     * parameters={
     *   {"name"="id", "dataType"="integer", "required"=true, "description"="user ID"}
     * }
     * )
     *
     * @param $id
     *
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="5")
     *
     * @View(serializerGroups={"slight-list"})
     */
    public function getRewardsAction($id, $page = 1, $limit = 5)
    {
        $query = $this->getDoctrine()->getManager()->createQuery('SELECT reward FROM AdEntifyCoreBundle:Reward reward
            LEFT JOIN reward.brand brand WHERE reward.owner = :userId AND
              ((SELECT COUNT(r.id) FROM AdEntifyCoreBundle:Reward r LEFT JOIN r.brand b WHERE b.id = brand.id) = 1 OR reward.type != :type)')
            ->setParameters(array(
                'userId'=> $id,
                'type' => Reward::TYPE_ADDICT
            ))
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $count = count($paginator);

        $rewards = null;
        $pagination = null;
        if ($count > 0) {
            $rewards = array();
            foreach ($paginator as $reward)
                $rewards[] = $reward;

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this,
                'api_v1_get_user_rewards', array(
                    'id' => $id
                ));
        }

        return PaginationTools::getPaginationArray($rewards, $pagination);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="GET actions for current logged user",
     *  output="AdEntify\CoreBundle\Entity\Action",
     *  section="User",
     * parameters={
     *   {"name"="id", "dataType"="integer", "required"=true, "description"="user ID"}
     * }
     * )
     *
     * @param $id
     *
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="30")
     *
     * @View(serializerGroups={"list"})
     */
    public function getActionsAction($page = 1, $limit = 30)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $em = $this->getDoctrine()->getManager();

            $query = $em->createQuery('SELECT action FROM AdEntify\CoreBundle\Entity\Action action
            LEFT JOIN action.target target LEFT JOIN action.author author
            WHERE author.id = :userId
            ORDER BY action.createdAt DESC')
                ->setParameters(array(
                    'userId' => $securityContext->getToken()->getUser()->getId()
                ))
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);

            $paginator = new Paginator($query, $fetchJoinCollection = true);
            $count = count($paginator);

            $actions = null;
            $pagination = null;
            if ($count > 0) {
                $actions = array();
                foreach($paginator as $action) {
                    $actions[] = $action;
                }

                $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_user_actions');
            }

            return PaginationTools::getPaginationArray($actions, $pagination);
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @View()
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Register a new user",
     *  output="AdEntify\CoreBundle\Entity\User",
     *  section="User"
     * )
     *
     * @param Request $request
     */
    public function postRegisterAction(Request $request) {
        /** @var $formFactory \FOS\UserBundle\Form\Factory\FactoryInterface */
        $formFactory = $this->container->get('fos_user.registration.form.factory');
        /** @var $userManager \FOS\UserBundle\Model\UserManagerInterface */
        $userManager = $this->container->get('fos_user.user_manager');
        /** @var $dispatcher \Symfony\Component\EventDispatcher\EventDispatcherInterface */
        $dispatcher = $this->container->get('event_dispatcher');

        $user = $userManager->createUser();
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $formFactory->createForm();
        $form->setData($user);

        if ('POST' === $request->getMethod()) {
            $form->bind($request);

            if ($form->isValid()) {
                $event = new FormEvent($form, $request);
                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

                $userManager->updateUser($user);

                if (null === $response = $event->getResponse()) {
                    $url = $this->container->get('router')->generate('fos_user_registration_confirmed');
                    $response = new RedirectResponse($url);
                }

                $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

                return $user;
            } else {
                $errors = $this->getErrorsAsArray($form);
                return array(
                    'success' => false,
                    'errors' => $errors
                );
            }
        }

        return $this->container->get('templating')->renderResponse('FOSUserBundle:Registration:register.html.'.$this->getEngine(), array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @View(serializerGroups={"details"})
     */
    public function postIntroPlayedAction()
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $securityContext->getToken()->getUser();
            $user->setIntroPlayed(true);
            $em = $this->getDoctrine()->getManager();
            $em->merge($user);
            $em->flush();
            return $user;
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *
     * @View(serializerGroups={"details"})
     */
    public function postSettingsAction(Request $request)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            if ($request->request->has('settings')) {
                $user = $securityContext->getToken()->getUser();
                $user->setSettings($request->request->get('settings'));
                $em = $this->getDoctrine()->getManager();
                $em->merge($user);
                $em->flush();
                return $user;
            } else {
                throw new HttpException(404);
            }
        } else {
            throw new HttpException(401);
        }
    }

    private function getErrorsAsArray(\Symfony\Component\Form\Form $form)
    {
        $errors = array();

        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $child) {
            if ($err = $this->getErrorsAsArray($child)) {
                $errors = array_merge($errors, $err);
            }
        }

        return $errors;
    }

    private function formatCredit(TagPoint $tagPoint = null, TagIncome $tagIncome = null)
    {
        $obj = $tagPoint ? $tagPoint : $tagIncome;

        return array(
            'type' => $obj->getTag()->getType(),
            'tagId' => $obj->getTag()->getId(),
            'photo' => $obj->getTag()->getPhoto()->getCaption(),
            'photoId' => $obj->getTag()->getPhoto()->getId(),
            'date' => $obj->getCreatedAt(),
            'brand' => $obj->getTag()->getBrand() ? $obj->getTag()->getBrand()->getName() : '',
            'brandSlug' => $obj->getTag()->getBrand() ? $obj->getTag()->getBrand()->getSlug() : '',
            'points' => $tagPoint ? $tagPoint->getPoints() : 0,
            'income' => $tagIncome ? $tagIncome->getIncome() : 0
        );
    }
}