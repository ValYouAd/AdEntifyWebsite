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
use AdEntify\CoreBundle\Entity\TagIncome;
use AdEntify\CoreBundle\Entity\TagPoint;
use AdEntify\CoreBundle\Entity\Task;
use AdEntify\CoreBundle\Form\VenueType;
use AdEntify\CoreBundle\Util\UserCacheManager;
use Doctrine\ORM\Query\ResultSetMapping;
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
use JMS\SecurityExtraBundle\Annotation\Secure;

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
     * @View()
     */
    public function getFavoritesAction()
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->container->get('security.context')->getToken()->getUser();
            return $user->getFavoritePhotos();
        } else {
            throw new HttpException(401);
        }
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
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $em = $this->getDoctrine()->getManager();
            $user = $securityContext->getToken()->getUser();

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
        } else {
            throw new HttpException(401);
        }
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

                return $follower;
            } else {
                $follower->removeFollowing($following);
                $follower->setFollowingsCount($follower->getFollowingsCount() - 1);
                $following->setFollowersCount($following->getFollowersCount() - 1);
                $em->merge($follower);
                $em->merge($following);
                $em->flush();

                // Empty followings cache
                UserCacheManager::getInstance()->deleteUserObject($follower, UserCacheManager::USER_CACHE_KEY_FOLLOWINGS);
            }
        } else {
            throw new HttpException(401);
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
        } else
            throw new HttpException(401);
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
     * @View(serializerGroups={"slight-list"})
     */
    public function getNotificationsAction($id)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $user = $this->container->get('security.context')->getToken()->getUser();
            if ($user->getId() == $id) {
                return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Notification')->findBy(array(
                    'owner' => $user->getId()
                ), array(
                    'createdAt' => 'DESC'
                ), 10);
            } else
                throw new HttpException(403);
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @View()
     *
     * @param $id
     */
    public function postChangePasswordAction($id, Request $request)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
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
                throw new HttpException(403);
        } else {
            throw new HttpException(401);
        }
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

    /**
     * @View()
     */
    public function getFollowersAction($id)
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
    public function getTopFollowersAction()
    {
        return $this->getDoctrine()->getManager()->createQuery('SELECT user FROM AdEntify\CoreBundle\Entity\User user
              ORDER BY user.followersCount')
            ->setMaxResults(10)
            ->getResult();
    }

    /**
     * @View()
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
                /*$this->getDoctrine()->getManager()->flush();*/
                return true;
            } else {
                throw new HttpException(403);
            }
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @View()
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
            return $task ? $task->getProgress() : null;
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @View()
     */
    public function postBirthdayAction()
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $currentUser = $this->container->get('security.context')->getToken()->getUser();
            $currentUser->setBirthday(new \DateTime($this->getRequest()->request->get('birthday')));
            $this->getDoctrine()->getManager()->merge($currentUser);
            $this->getDoctrine()->getManager()->flush();
            return '';
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @View()
     */
    public function getBrandsAction()
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->getDoctrine()->getManager()->createQuery('SELECT b FROM AdEntifyCoreBundle:Brand as b
            LEFT JOIN b.followers as follower WHERE b.validated = 1 AND follower.id = :currentUserId')
                ->setParameters(array(
                    'currentUserId' => $securityContext->getToken()->getUser()->getId()
                ))->getResult();
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @View()
     */
    public function getAnalyticsAction()
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $em = $this->getDoctrine()->getManager();
            $currentUser = $this->container->get('security.context')->getToken()->getUser();

            $taggedPhotos = $em->createQuery('SELECT COUNT(p.id) FROM AdEntifyCoreBundle:Photo p WHERE p.owner = :currentUserId
            AND p.tags IS NOT EMPTY')
                ->setParameters(array(
                    'currentUserId' => $currentUser->getId()
                ))
                ->getSingleScalarResult();

            $untaggedPhotos = $em->createQuery('SELECT COUNT(p.id) FROM AdEntifyCoreBundle:Photo p WHERE p.owner = :currentUserId
            AND p.tags IS EMPTY')
                ->setParameters(array(
                    'currentUserId' => $currentUser->getId()
                ))
                ->getSingleScalarResult();

            $totalPhotos = $em->createQuery('SELECT COUNT(p.id) FROM AdEntifyCoreBundle:Photo p WHERE p.owner = :currentUserId')
                ->setParameters(array(
                    'currentUserId' => $currentUser->getId()
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
     * @View()
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
     * @View()
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
     * @View()
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
     * @param $id
     *
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="10")
     *
     * @View()
     */
    public function getRewardsAction($id, $page = 1, $limit = 10)
    {
        $query = $this->getDoctrine()->getManager()->createQuery('SELECT reward FROM AdEntifyCoreBundle:Reward reward
            WHERE reward.owner = :userId')
            ->setParameters(array(
                'userId'=> $id
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
     * @param $id
     *
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="20")
     *
     * @View()
     */
    public function getActionsAction($page = 1, $limit = 20)
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