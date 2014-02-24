<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 18/04/2013
 * Time: 18:46
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\Action;
use AdEntify\CoreBundle\Entity\Notification;
use AdEntify\CoreBundle\Entity\SearchHistory;
use AdEntify\CoreBundle\Entity\Tag;
use AdEntify\CoreBundle\Form\PhotoType;
use AdEntify\CoreBundle\Util\CommonTools;
use AdEntify\CoreBundle\Util\PaginationTools;
use AdEntify\CoreBundle\Util\UserCacheManager;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection;

use AdEntify\CoreBundle\Entity\Photo;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class PhotosController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("Photo")
 */
class PhotosController extends FosRestController
{
    /**
     * GET all public or friends photos
     *
     * @View(serializerGroups={"list"})
     * @QueryParam(name="tagged", default="true")
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="30")
     * @QueryParam(name="brands", requirements="\d+", default="null")
     * @QueryParam(name="places", requirements="\d+", default="null")
     * @QueryParam(name="people", requirements="\d+", default="null")
     * @QueryParam(name="orderBy", default="null")
     * @QueryParam(name="way", default="DESC")
     */
    public function cgetAction($tagged, $page, $limit, $brands, $places, $people, $orderBy, $way)
    {
        $em = $this->getDoctrine()->getManager();

        $securityContext = $this->container->get('security.context');
        $user = null;
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->container->get('security.context')->getToken()->getUser();
        }

        // If no user connected, 0 is default
        $facebookFriendsIds = array(0);
        $followings = array(0);
        $followedBrands = array(0);

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

            // Get following brands ids
            $followedBrands = UserCacheManager::getInstance()->getUserObject($user, UserCacheManager::USER_CACHE_KEY_BRAND_FOLLOWINGS);
            if (!$followedBrands) {
                $followedBrands = $em->getRepository('AdEntifyCoreBundle:User')->getFollowedBrandsIds($user);
                UserCacheManager::getInstance()->setUserObject($user, UserCacheManager::USER_CACHE_KEY_BRAND_FOLLOWINGS, $followedBrands, UserCacheManager::USER_CACHE_TTL_BRAND_FOLLOWINGS);
            }
        }

        $parameters = null;
        $countQuery = null;
        $dataQuery = null;
        $joinClause = null;

        if ($tagged == 'true') {
            $parameters = array(
                ':status' => Photo::STATUS_READY,
                ':visibilityScope' => Photo::SCOPE_PUBLIC,
                ':facebookFriendsIds' => $facebookFriendsIds,
                ':followings' => $followings,
                ':followedBrands' => $followedBrands,
                ':none' => Tag::VALIDATION_NONE,
                ':granted' => Tag::VALIDATION_GRANTED
            );

            $tagClause = '';
            if ($brands == 1) {
                $tagClause = ' AND tag.brand IS NOT NULL';
            }
            if ($places == 1) {
                $tagClause = ' AND tag.venue IS NOT NULL';
            }
            if ($people == 1) {
                $tagClause = ' AND tag.person IS NOT NULL';
            }

            $sql = sprintf('SELECT photo, tag FROM AdEntify\CoreBundle\Entity\Photo photo
                INNER JOIN photo.tags tag WITH (tag.visible = true AND tag.deletedAt IS NULL
                  AND tag.censored = false AND tag.waitingValidation = false
                  AND (tag.validationStatus = :none OR tag.validationStatus = :granted) %s)
                INNER JOIN photo.owner owner LEFT JOIN tag.brand brand %s
                WHERE photo.status = :status AND photo.deletedAt IS NULL AND (photo.visibilityScope = :visibilityScope
                OR (owner.facebookId IS NOT NULL AND owner.facebookId IN (:facebookFriendsIds)) OR owner.id IN (:followings) OR brand.id IN (:followedBrands))
                AND photo.tagsCount > 0', $tagClause, $joinClause);
        } else {
            $parameters = array(
                ':status' => Photo::STATUS_READY,
                ':visibilityScope' => Photo::SCOPE_PUBLIC,
                ':facebookFriendsIds' => $facebookFriendsIds,
                ':followings' => $followings,
                ':followedBrands' => $followedBrands
            );
            $sql = sprintf('SELECT DISTINCT photo FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.owner owner LEFT JOIN photo.tags tag LEFT JOIN tag.brand brand %s
                WHERE photo.status = :status AND photo.deletedAt IS NULL AND (photo.visibilityScope = :visibilityScope
                  OR (owner.facebookId IS NOT NULL AND owner.facebookId IN (:facebookFriendsIds)) OR owner.id IN (:followings) OR brand.id IN (:followedBrands))
                AND photo.tagsCount = 0 ', $joinClause);
        }

        if ($orderBy) {
            switch ($orderBy) {
                case 'likes':
                    $sql .= sprintf(' ORDER BY photo.likesCount %s', $way);
                    break;
                case 'points':
                    $sql .= sprintf(' ORDER BY photo.totalTagsPoints %s', $way);
                    break;
                case 'date':
                default:
                $sql .= sprintf(' ORDER BY photo.createdAt %s', $way);
                    break;
            }
        } else {
            $sql .= sprintf(' ORDER BY photo.createdAt %s', $way);
        }

        $query = $em->createQuery($sql)
            ->setParameters($parameters)
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

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_photos', array(
                'tagged' => $tagged
            ));
        }

        return PaginationTools::getPaginationArray($photos, $pagination);
    }

    /**
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Get a photo",
     *  output="AdEntify\CoreBundle\Entity\Photo",
     *  section="Photo"
     * )
     *
     * @param integer $id photo id
     * @View(serializerGroups={"details"})
     * @return Photo
     */
    public function getAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $securityContext = $this->container->get('security.context');
        $user = null;
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->container->get('security.context')->getToken()->getUser();
        }

        // If no user connected, 0 is default
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

        $photo = $em->createQuery('SELECT photo FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.owner owner
                WHERE photo.id = :id AND photo.deletedAt IS NULL AND photo.status = :status
                AND (photo.owner = :currentUserId OR photo.visibilityScope = :visibilityScope OR (owner.facebookId IS NOT NULL
                AND owner.facebookId IN (:facebookFriendsIds)) OR owner.id IN (:followings))
                ORDER BY photo.createdAt DESC')
            ->setParameters(array(
                ':status' => Photo::STATUS_READY,
                ':visibilityScope' => Photo::SCOPE_PUBLIC,
                ':id' => $id,
                ':facebookFriendsIds' => $facebookFriendsIds,
                ':followings' => $followings,
                ':currentUserId' => $user ? $user->getId() : 0,
            ))
            ->getOneOrNullResult();

        $criteria = Criteria::create()
            ->where(Criteria::expr()->isNull('deletedAt'))
            ->andWhere(Criteria::expr()->eq('censored', false))
            ->andWhere(Criteria::expr()->eq('waitingValidation', false))
            ->andWhere(Criteria::expr()->eq('censored', false))
            ->andWhere(Criteria::expr()->eq('visible', true))
            ->andWhere(Criteria::expr()->orX(
                Criteria::expr()->eq('validationStatus', Tag::VALIDATION_NONE),
                Criteria::expr()->eq('validationStatus', Tag::VALIDATION_GRANTED)
            ));

        $photo->setTags($photo->getTags()->matching($criteria));

        if ($photo)
            return $photo;
        else
            throw new NotFoundHttpException('Photo not found');
    }

    /**
     * @param $query
     * @param int $limit
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Search a photo with a query (keyword)",
     *  output="AdEntify\CoreBundle\Entity\Tag",
     *  section="Photo"
     * )
     *
     * @QueryParam(name="query")
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="10")
     * @QueryParam(name="today", requirements="\d+", default="null")
     * @QueryParam(name="orderBy", default="null")
     * @QueryParam(name="way", default="DESC")
     * @View(serializerGroups={"list"})
     */
    public function getSearchAction($query, $page = 1, $limit = 10, $today = null, $orderBy = null, $way = 'DESC', Request $request)
    {
        if (!$query)
            return null;

        $em = $this->getDoctrine()->getManager();

        $securityContext = $this->container->get('security.context');
        $user = null;
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->container->get('security.context')->getToken()->getUser();
        }

        // Historique de recherche
        $searchHistory = new SearchHistory();
        $searchHistory->setKeywords($query)->setIpAddress($request->getClientIp());
        if ($user)
            $searchHistory->setUser($user);
        $em->persist($searchHistory);
        $em->flush();

        // Extract hashtags from query
        $hashtags = CommonTools::extractHashtags($query, false, true);

        // If no user connected, 0 is default
        $facebookFriendsIds = array(0);
        $followings = array(0);
        $followedBrands = array(0);

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

            // Get following brands ids
            $followedBrands = UserCacheManager::getInstance()->getUserObject($user, UserCacheManager::USER_CACHE_KEY_BRAND_FOLLOWINGS);
            if (!$followedBrands) {
                $followedBrands = $em->getRepository('AdEntifyCoreBundle:User')->getFollowedBrandsIds($user);
                UserCacheManager::getInstance()->setUserObject($user, UserCacheManager::USER_CACHE_KEY_BRAND_FOLLOWINGS, $followedBrands, UserCacheManager::USER_CACHE_TTL_BRAND_FOLLOWINGS);
            }
        }

        // Order by
        if ($orderBy) {
            switch ($orderBy) {
                case 'likes':
                    $orderByQuery = sprintf(' ORDER BY photo.likesCount %s', $way);
                    break;
                case 'date':
                default:
                    $orderByQuery = sprintf(' ORDER BY photo.createdAt %s', $way);
                    break;
            }
        } else {
            $orderByQuery = sprintf(' ORDER BY photo.createdAt %s', $way);
        }

        $whereClauses = array();
        $parameters = array();
        if ($today == 1) {
            $whereClauses[] = ' AND DATE(photo.createdAt) = DATE(:currentDate) ';
            $parameters['currentDate'] = new \DateTime();
        }

        // If hashtags found, search photo with this hashtag(s)
        if (count($hashtags) > 0) {
            $query = $em->createQuery('SELECT photo FROM AdEntify\CoreBundle\Entity\Photo photo
            INNER JOIN photo.owner owner LEFT JOIN photo.hashtags hashtag LEFT JOIN photo.tags tag LEFT JOIN tag.brand brand
            WHERE photo.status = :status AND photo.deletedAt IS NULL AND (photo.visibilityScope = :visibilityScope
                OR (owner.facebookId IS NOT NULL AND owner.facebookId IN (:facebookFriendsIds)) OR owner.id IN (:followings) or brand.id IN (:followedBrands))
            AND hashtag.name IN (:hashtags)' . implode('', $whereClauses) . $orderByQuery)
                ->setParameters(array_merge(array(
                    ':status' => Photo::STATUS_READY,
                    ':visibilityScope' => Photo::SCOPE_PUBLIC,
                    ':facebookFriendsIds' => $facebookFriendsIds,
                    ':followings' => $followings,
                    ':followedBrands' => $followedBrands,
                    ':hashtags' => $hashtags
                ), $parameters))
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);
        } else {
            $query = $em->createQuery('SELECT photo, tag FROM AdEntify\CoreBundle\Entity\Photo photo
            LEFT JOIN photo.tags tag INNER JOIN photo.owner owner LEFT JOIN tag.venue venue LEFT JOIN tag.person person
            LEFT JOIN tag.product product LEFT JOIN photo.hashtags hashtag LEFT JOIN tag.brand brand
            WHERE photo.status = :status AND photo.deletedAt IS NULL AND (photo.visibilityScope = :visibilityScope
                OR (owner.facebookId IS NOT NULL AND owner.facebookId IN (:facebookFriendsIds)) OR owner.id IN (:followings) OR brand.id IN (:followedBrands))
            AND tag.deletedAt IS NULL AND tag.censored = FALSE AND tag.waitingValidation = FALSE AND (tag.validationStatus = :none OR tag.validationStatus = :granted) AND
            (LOWER(tag.title) LIKE LOWER(:query) OR LOWER(venue.name) LIKE LOWER(:query) OR LOWER(person.firstname)
            LIKE LOWER(:query) OR LOWER(person.lastname) LIKE LOWER(:query) OR LOWER(product.name) LIKE LOWER(:query)
            OR LOWER(brand.name) LIKE LOWER(:query) OR hashtag.name LIKE LOWER(:query))' . implode('', $whereClauses) . $orderByQuery)
                ->setParameters(array_merge(array(
                    ':query' => '%'.$query.'%',
                    ':none' => Tag::VALIDATION_NONE,
                    ':granted' => Tag::VALIDATION_GRANTED,
                    ':status' => Photo::STATUS_READY,
                    ':visibilityScope' => Photo::SCOPE_PUBLIC,
                    ':facebookFriendsIds' => $facebookFriendsIds,
                    ':followings' => $followings,
                    ':followedBrands' => $followedBrands
                ), $parameters))
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit);
        }

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $count = count($paginator);

        $photos = null;
        $pagination = null;
        if ($count > 0) {
            $photos = array();
            foreach($paginator as $photo) {
                $photos[] = $photo;
            }

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_photo_search', array(
                'query' => $query
            ));
        }

        return PaginationTools::getPaginationArray($photos, $pagination);
    }

    /**
     * @View(serializerGroups={"list"})
     *
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="10")
     *
     * @param $id
     */
    public function getLinkedPhotosAction($id, $page = 1, $limit = 10)
    {
        $em = $this->getDoctrine()->getManager();
        $user = null;
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            $user = $this->container->get('security.context')->getToken()->getUser();
        }

        // Get photo categories
        $categoriesId = $em->createQuery('SELECT category.id FROM AdEntify\CoreBundle\Entity\Category category
            JOIN category.photos photo WHERE photo.id = :photoId')
            ->setParameters(array(
                'photoId' => $id
            ))->getResult();
        if (!$categoriesId) {
            $categoriesId = array(0);
        }

        // Get friends list (id) array
        $facebookFriendsIds = array(0);
        $followings = array(0);
        if ($user) {
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

        $sql = 'SELECT photo, tag FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.tags tag WITH (tag.visible = true AND tag.deletedAt IS NULL
                  AND tag.censored = false AND tag.waitingValidation = false
                  AND (tag.validationStatus = :none OR tag.validationStatus = :granted))
                LEFT JOIN photo.owner owner LEFT JOIN photo.categories category
                WHERE category.id IN (:categories) AND photo.id != :photoId AND photo.status = :status AND photo.deletedAt IS NULL
                    AND (photo.owner = :currentUserId OR photo.visibilityScope = :visibilityScope OR (owner.facebookId IS NOT NULL AND owner.facebookId IN (:facebookFriendsIds))
                    OR owner.id IN (:followings)) ORDER BY photo.createdAt DESC';

        $query = $em->createQuery($sql)->setParameters(array(
                'categories' => $categoriesId,
                ':status' => Photo::STATUS_READY,
                ':visibilityScope' => Photo::SCOPE_PUBLIC,
                ':currentUserId' => $user ? $user->getId() : 0,
                ':facebookFriendsIds' => $facebookFriendsIds,
                ':followings' => $followings,
                ':photoId' => $id,
                ':none' => Tag::VALIDATION_NONE,
                ':granted' => Tag::VALIDATION_GRANTED,
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

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_photo_linked_photos', array(
                'id' => $id
            ));
        }

        return PaginationTools::getPaginationArray($photos, $pagination);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Post a Photo",
     *  input="AdEntify\CoreBundle\Form\PhotoType",
     *  output="AdEntify\CoreBundle\Entity\Photo",
     *  statusCodes={
     *      200="Returned if the photo is created"
     *  },
     *  section="Photo"
     * )
     *
     * @View()
     */
    public function postAction(Request $request)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $photo = new Photo();
            $form = $this->getForm($photo);
            $form->bind($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                // Get current user
                $user = $this->container->get('security.context')->getToken()->getUser();

                $photo->setOwner($user)->setStatus(Photo::STATUS_READY)->setVisibilityScope(Photo::SCOPE_PUBLIC);

                $em->persist($photo);
                $em->flush();

                return $photo;
            } else {
                return $form;
            }
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Edit a Photo",
     *  input="AdEntify\CoreBundle\Form\PhotoType",
     *  output="AdEntify\CoreBundle\Entity\Photo",
     *  section="Photo"
     * )
     *
     * @View()
     */
    public function putAction($id, Request $request)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $photo = $this->getAction($id);
            if ($photo) {
                $user = $this->container->get('security.context')->getToken()->getUser();
                if ($photo->getOwner()->getId() == $user->getId()) {
                    $form = $this->getForm($photo);
                    $form->bind($request);
                    if ($form->isValid()) {
                        $em = $this->getDoctrine()->getManager();
                        echo count($photo->getHashtags());
                        $em->merge($photo);
                        $em->flush();
                        return $photo;
                    } else {
                        return $form;
                    }
                } else
                    throw new HttpException(403, 'You are not authorized to edit this photo');
            } else
                throw new NotFoundHttpException('Photo not found');
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * Delete a photo
     *
     * @View()
     *
     * @param $id
     */
    public function deleteAction($id)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $photo = $this->getAction($id);
            $user = $this->container->get('security.context')->getToken()->getUser();
            // Check if current user is the owner oh the photo and that no tags are link to the photo
            if ($user->getId() == $photo->getOwner()->getId() && count($photo->getTags()) == 0) {
                $em = $this->getDoctrine()->getManager();
                $photo->setDeletedAt(new \DateTime());

                // Delete actions related to this photo
                $actions = $em->createQuery('SELECT action FROM AdEntify\CoreBundle\Entity\Action action
                    LEFT JOIN action.photos photo WHERE photo.id = :photoId OR (action.linkedObjectId = :photoId AND action.linkedObjectType = :linkedObjectType)')
                    ->setParameters(array(
                        ':photoId' => $id,
                        'linkedObjectType' => 'AdEntify\CoreBundle\Entity\Photo'
                    ))->getResult();
                if (count($actions) > 0) {
                    foreach($actions as $action) {
                        $em->remove($action);
                    }
                }

                // Delete notifications
                $notifications = $em->createQuery('SELECT notif FROM AdEntify\CoreBundle\Entity\Notification notif
                    LEFT JOIN notif.photos photo WHERE photo.id = :photoId OR (notif.objectId = :photoId AND notif.objectType = :linkedObjectType)')
                    ->setParameters(array(
                        ':photoId' => $id,
                        'linkedObjectType' => 'AdEntify\CoreBundle\Entity\Photo'
                    ))->getResult();
                if (count($notifications) > 0) {
                    foreach($notifications as $notification) {
                        $em->remove($notification);
                    }
                }

                $em->merge($photo);
                $em->flush();
            } else {
                throw new HttpException(403, 'You are not authorized to delete this photo');
            }
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * GET all tags by photo ID
     *
     * @View(serializerGroups={"details"})
     * @param $id
     * @return ArrayCollection|null
     */
    public function getTagsAction($id)
    {
        return $this->getDoctrine()->getManager()->createQuery('SELECT tag FROM AdEntify\CoreBundle\Entity\Tag tag
                LEFT JOIN tag.photo photo WHERE photo.id = :id AND tag.visible = TRUE AND tag.deletedAt IS NULL
                  AND tag.censored = FALSE AND tag.waitingValidation = FALSE AND (tag.validationStatus = :none OR tag.validationStatus = :granted)')
            ->setParameters(array(
                ':id' => $id,
                ':none' => Tag::VALIDATION_NONE,
                ':granted' => Tag::VALIDATION_GRANTED
            ))
            ->getResult();
    }

    /**
     * @View(serializerGroups={"details"})
     *
     * @param $id
     */
    public function getWaitingTagsAction($id)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $em = $this->getDoctrine()->getManager();
            $user = $this->container->get('security.context')->getToken()->getUser();

            return $em->createQuery('SELECT tag FROM AdEntify\CoreBundle\Entity\Tag tag
                LEFT JOIN tag.photo photo LEFT JOIN photo.owner as owner
                WHERE photo.id = :id and owner.id = :userId AND tag.visible = TRUE AND tag.deletedAt IS NULL
                AND tag.censored = FALSE AND tag.waitingValidation = TRUE and tag.validationStatus = :validationStatus')
                ->setParameters(array(
                    ':id' => $id,
                    ':validationStatus' => Tag::VALIDATION_WAITING,
                    ':userId' => $user->getId()
                ))
                ->getResult();
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * Get photo hashtags
     *
     * @param $id
     *
     * @View()
     */
    public function getHashtagsAction($id)
    {
        return $this->getDoctrine()->getManager()->createQuery('SELECT hashtag FROM AdEntify\CoreBundle\Entity\Hashtag hashtag
            LEFT JOIN hashtag.photos photo
            WHERE photo.id = :id')
            ->setParameters(array(
                'id'=> $id
            ))
            ->getResult();
    }

    /**
     * GET all comments by photo ID
     *
     * @View()
     *
     * @param $id
     * @return ArrayCollection|null
     */
    public function getCommentsAction($id)
    {
        return $this->getDoctrine()->getManager()->createQuery('SELECT comment FROM AdEntifyCoreBundle:Comment comment
            WHERE comment.deletedAt IS NULL AND comment.photo = :photoId')
            ->setParameters(array(
                'photoId' => $id
            ))->getResult();
    }

    /**
     * GET all likers by photo ID
     *
     * @View()
     *
     * @param $id
     * @return ArrayCollection|null
     */
    public function getLikersAction($id)
    {
        return $this->getDoctrine()->getManager()->createQuery('SELECT user FROM AdEntifyCoreBundle:User user
            LEFT JOIN user.likes l WHERE l.photo = :photoId')
            ->setParameters(array(
                'photoId' => $id
            ))->getResult();
    }

    /**
     * GET all categories by photo ID
     *
     * @View()
     * @QueryParam(name="locale", default="en")
     *
     * @param $id
     * @return ArrayCollection|null
     */
    public function getCategoriesAction($id, $locale = 'en')
    {
        return $this->getDoctrine()->getManager()
            ->createQuery("SELECT category FROM AdEntify\CoreBundle\Entity\Category category
                LEFT JOIN category.photos photo WHERE photo.id = :id AND category.visible = 1")
            ->setParameter('id', $id)
            ->useQueryCache(false)
            ->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale)
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_FALLBACK, 1)
            ->getResult();
    }

    /**
     * @View()
     *
     * @param $id
     * @return ArrayCollection|null
     */
    public function getLikesAction($id)
    {
        $photo = $this->getAction($id);
        if (!$photo)
            return null;
        return $photo->getLikes();
    }

    /**
     * @View()
     *
     * @param $id
     * @return bool
     */
    public function getIsLikedAction($id)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->container->get('security.context')->getToken()->getUser();

            $count = $this->getDoctrine()->getManager()->createQuery('SELECT COUNT(l.id) FROM AdEntify\CoreBundle\Entity\Like l
              WHERE l.photo = :photoId AND l.liker = :userId')
                ->setParameters(array(
                    'photoId' => $id,
                    'userId' => $user->getId()
                ))
                ->getSingleScalarResult();

            return $count > 0 ? true : false;
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @View()
     *
     * @param $id
     * @return bool
     */
    public function getIsFavoritesAction($id)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->container->get('security.context')->getToken()->getUser();

            $count = $this->getDoctrine()->getManager()->createQuery('SELECT COUNT(p.id) FROM AdEntify\CoreBundle\Entity\User u
              LEFT JOIN u.favoritesPhotos p
              WHERE p.id = :photoId AND u.id = :userId')
                ->setParameters(array(
                    'photoId' => $id,
                    'userId' => $user->getId()
                ))
                ->getSingleScalarResult();

            return $count > 0 ? true : false;
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @View()
     * @QueryParam(name="tagged", default="true")
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="20")
     *
     * @param $id
     * @return ArrayCollection|null
     */
    public function getUserPhotosAction($tagged, $page = 1, $limit = 20)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $em = $this->getDoctrine()->getManager();
            $user = $this->container->get('security.context')->getToken()->getUser();

            $count = $em->createQuery('SELECT COUNT(photo.id) FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.tags tag
                WHERE photo.owner = :userId AND photo.deletedAt IS NULL AND photo.status = :status AND '.($tagged == 'true' ? 'photo.tagsCount > 0 AND tag.visible = true
                AND tag.deletedAt IS NULL AND tag.censored = FALSE AND tag.waitingValidation = FALSE' : 'photo.tagsCount = 0'))
                ->setParameters(array(
                    ':userId' => $user->getId(),
                    ':status' => Photo::STATUS_READY
                ))
                ->getSingleScalarResult();

            $photos = null;
            $pagination = null;
            if ($count > 0) {
                $photos = $em->createQuery('SELECT photo, tag FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.tags tag
                WHERE photo.owner = :userId AND photo.deletedAt IS NULL AND photo.status = :status AND '.($tagged == 'true' ? 'photo.tagsCount > 0 AND tag.visible = true
                AND tag.deletedAt IS NULL AND tag.censored = FALSE AND tag.waitingValidation = FALSE' : 'photo.tagsCount = 0').
                    ' ORDER BY photo.createdAt DESC')
                    ->setParameters(array(
                        ':userId' => $user->getId(),
                        ':status' => Photo::STATUS_READY
                    ))
                    ->setFirstResult(($page - 1) * $limit)
                    ->setMaxResults($limit)
                    ->getResult();

                $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_photo_user_photos', array(
                    'tagged' => $tagged
                ));
            }

            return PaginationTools::getPaginationArray($photos, $pagination);
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * Get form for photo
     *
     * @param null $photo
     * @return mixed
     */
    protected function getForm($photo = null)
    {
        return $this->createForm(new PhotoType(), $photo);
    }

    /**
     * @View()
     */
    public function postFavoriteAction(Request $request)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            if ($request->request->has('photoId') && is_numeric($request->request->get('photoId'))) {
                $photo = $this->getAction($request->request->get('photoId'));
                if ($photo) {
                    $user = $this->container->get('security.context')->getToken()->getUser();
                    $found = false;
                    $em = $this->getDoctrine()->getManager();

                    foreach($user->getFavoritePhotos() as $favoritePhoto) {
                        if ($favoritePhoto->getId() == $photo->getId())
                            $found = true; break;
                    }

                    if (!$found) {
                        // Add favorite
                        $user->addFavoritePhoto($photo);

                        // FAVORITE Action & notification
                        $sendNotification = $user->getId() != $photo->getOwner()->getId();
                        $em->getRepository('AdEntifyCoreBundle:Action')->createAction(Action::TYPE_PHOTO_FAVORITE,
                            $user, $photo->getOwner(), array($photo), Action::getVisibilityWithPhotoVisibility($photo->getVisibilityScope()), $photo->getId(),
                            $em->getClassMetadata(get_class($photo))->getName(), $sendNotification, 'photoFav');
                    } else {
                        $user->removeFavoritePhoto($photo);
                    }

                    $em->merge($user);
                    $em->flush();
                }
            }
        } else {
            throw new HttpException(401);
        }
    }
}