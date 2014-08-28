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
use AdEntify\CoreBundle\Model\Thumb;
use AdEntify\CoreBundle\Util\CommonTools;
use AdEntify\CoreBundle\Util\FileTools;
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
     * @ApiDoc(
     *  resource=true,
     *  description="Get all punlic or friends photos",
     *  output="AdEntify\CoreBundle\Entity\Photo",
     *  section="Photo"
     * )
     *
     * GET all public or friends photos
     *
     * @View(serializerGroups={"list"})
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="30")
     * @QueryParam(name="brands", requirements="\d+", default="null")
     * @QueryParam(name="places", requirements="\d+", default="null")
     * @QueryParam(name="people", requirements="\d+", default="null")
     * @QueryParam(name="orderBy", default="null")
     * @QueryParam(name="way", requirements="DESC|ASC", default="DESC")
     */
    public function cgetAction($page, $limit, $brands, $places, $people, $orderBy, $way)
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
                $followings = $em->getRepository('AdEntifyCoreBundle:User')->getFollowingsIds($user, 0);
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


        $parameters = array(
            ':status' => Photo::STATUS_READY,
            ':visibilityScope' => Photo::SCOPE_PUBLIC,
            ':facebookFriendsIds' => $facebookFriendsIds,
            ':followings' => $followings,
            ':followedBrands' => $followedBrands,
            ':denied' => Tag::VALIDATION_DENIED
        );

        $tagClause = '';
        $joinSide = 'LEFT';
        if ($brands == 1) {
            $tagClause = ' AND tag.brand IS NOT NULL';
            $joinSide = 'INNER';
        }
        if ($places == 1) {
            $tagClause = ' AND tag.venue IS NOT NULL AND tag.product IS NULL';
            $joinSide = 'INNER';
        }
        if ($people == 1) {
            $tagClause = ' AND tag.person IS NOT NULL';
            $joinSide = 'INNER';
        }

        $sql = sprintf('SELECT photo, tag FROM AdEntify\CoreBundle\Entity\Photo photo
            ' . $joinSide . ' JOIN photo.tags tag WITH (tag.visible = true AND tag.deletedAt IS NULL
              AND tag.censored = false AND tag.validationStatus != :denied %s)
            INNER JOIN photo.owner owner LEFT JOIN tag.brand brand %s
            WHERE photo.tagsCount > 0 AND photo.status = :status AND photo.deletedAt IS NULL AND (photo.visibilityScope = :visibilityScope
            OR (owner.facebookId IS NOT NULL AND owner.facebookId IN (:facebookFriendsIds)) OR owner.id IN (:followings) OR brand.id IN (:followedBrands))', $tagClause, $joinClause);

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

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_photos');
        }

        return PaginationTools::getPaginationArray($photos, $pagination);
    }

    /**
     * Get a photo by id
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Get a photo",
     *  output="AdEntify\CoreBundle\Entity\Photo",
     *  section="Photo",
     *  parameters={
     *   {"name"="id", "dataType"="integer", "required"=true, "description"="photo ID"}
     *  }
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
                $followings = $em->getRepository('AdEntifyCoreBundle:User')->getFollowingsIds($user, 1);
                UserCacheManager::getInstance()->setUserObject($user, UserCacheManager::USER_CACHE_KEY_FOLLOWINGS, $followings, UserCacheManager::USER_CACHE_TTL_FOLLOWING);
            }

            // Get following brands ids
            $followedBrands = UserCacheManager::getInstance()->getUserObject($user, UserCacheManager::USER_CACHE_KEY_BRAND_FOLLOWINGS);
            if (!$followedBrands) {
                $followedBrands = $em->getRepository('AdEntifyCoreBundle:User')->getFollowedBrandsIds($user);
                UserCacheManager::getInstance()->setUserObject($user, UserCacheManager::USER_CACHE_KEY_BRAND_FOLLOWINGS, $followedBrands, UserCacheManager::USER_CACHE_TTL_BRAND_FOLLOWINGS);
            }
        }

        $photo = $em->createQuery('SELECT photo FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.owner owner LEFT JOIN photo.tags tag LEFT JOIN tag.brand brand
                WHERE photo.id = :id AND photo.deletedAt IS NULL AND photo.status = :status
                AND (photo.owner = :currentUserId OR photo.visibilityScope = :visibilityScope OR (owner.facebookId IS NOT NULL
                AND owner.facebookId IN (:facebookFriendsIds)) OR owner.id IN (:followings) OR brand.id IN (:followedBrands))
                ORDER BY photo.createdAt DESC')
            ->setParameters(array(
                ':status' => Photo::STATUS_READY,
                ':visibilityScope' => Photo::SCOPE_PUBLIC,
                ':id' => $id,
                ':facebookFriendsIds' => $facebookFriendsIds,
                ':followings' => $followings,
                ':followedBrands' => $followedBrands,
                ':currentUserId' => $user ? $user->getId() : 0,
            ))
            ->getOneOrNullResult();

        $criteria = Criteria::create()
            ->where(Criteria::expr()->isNull('deletedAt'))
            ->andWhere(Criteria::expr()->eq('censored', false))
            ->andWhere(Criteria::expr()->neq('validationStatus', Tag::VALIDATION_DENIED))
            ->andWhere(Criteria::expr()->eq('visible', true));

        if ($photo) {
            // Get last 3 comments
            $comments = $em->createQuery('SELECT comment FROM AdEntifyCoreBundle:Comment comment WHERE comment.photo = :photoId ORDER BY comment.createdAt DESC')
                ->setParameter('photoId', $photo->getId())
                ->setMaxResults(3)
                ->getResult();
            if (count($comments) > 0) {
                $photo->clearComments();
                foreach($comments as $comment) {
                    $photo->addComment($comment);
                }
            }
            // Get last 3 likes
            $likes = $em->createQuery('SELECT l FROM AdEntifyCoreBundle:Like l WHERE l.photo = :photoId AND l.deleted_at IS NULL ORDER BY l.createdAt DESC')
                ->setParameter('photoId', $photo->getId())
                ->setMaxResults(3)
                ->getResult();
            if (count($likes) > 0) {
                $photo->clearLikes();
                foreach($likes as $like) {
                    $photo->addLike($like);
                }
            }

            $photo->setTags($photo->getTags()->matching($criteria));
            return $photo;
        } else {
            $photo = $em->getRepository('AdEntifyCoreBundle:Photo')->find($id);
            if ($photo && $photo->getVisibilityScope() == Photo::SCOPE_PRIVATE) {
                throw new HttpException(403);
            } else {
                throw new NotFoundHttpException('Photo not found');
            }
        }
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
     * @QueryParam(name="way", requirements="DESC|ASC", default="DESC")
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
                $followings = $em->getRepository('AdEntifyCoreBundle:User')->getFollowingsIds($user, 0);
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
            AND tag.deletedAt IS NULL AND tag.censored = FALSE AND tag.validationStatus != :denied AND
            (LOWER(tag.title) LIKE LOWER(:query) OR LOWER(venue.name) LIKE LOWER(:query) OR LOWER(person.firstname)
            LIKE LOWER(:query) OR LOWER(person.lastname) LIKE LOWER(:query) OR LOWER(product.name) LIKE LOWER(:query)
            OR LOWER(brand.name) LIKE LOWER(:query) OR hashtag.name LIKE LOWER(:query)' . implode('', $whereClauses) . $orderByQuery)
                ->setParameters(array_merge(array(
                    ':query' => '%'.$query.'%',
                    ':denied' => Tag::VALIDATION_DENIED,
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
     * @ApiDoc(
     *  resource=true,
     *  description="Get linked photos",
     *  output="AdEntify\CoreBundle\Entity\Photo",
     *  statusCodes={
     *      401="Returned when authentication is required",
     *  },
     *  section="Photo",
     *  parameters={
     *   {"name"="id", "dataType"="integer", "required"=true, "description"="photo ID"}
     *  }
     * )
     *
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
        $followedBrands = array(0);

        if ($user) {
            $facebookFriendsIds = UserCacheManager::getInstance()->getUserObject($user, UserCacheManager::USER_CACHE_KEY_FB_FRIENDS);
            if (!$facebookFriendsIds) {
                $facebookFriendsIds = $em->getRepository('AdEntifyCoreBundle:User')->refreshFriends($user, $this->container->get('fos_facebook.api'));
                UserCacheManager::getInstance()->setUserObject($user, UserCacheManager::USER_CACHE_KEY_FB_FRIENDS, $facebookFriendsIds, UserCacheManager::USER_CACHE_TTL_FB_FRIENDS);
            }

            // Get followings ids
            $followings = UserCacheManager::getInstance()->getUserObject($user, UserCacheManager::USER_CACHE_KEY_FOLLOWINGS);
            if (!$followings) {
                $followings = $em->getRepository('AdEntifyCoreBundle:User')->getFollowingsIds($user, 1);
                UserCacheManager::getInstance()->setUserObject($user, UserCacheManager::USER_CACHE_KEY_FOLLOWINGS, $followings, UserCacheManager::USER_CACHE_TTL_FOLLOWING);
            }

            // Get following brands ids
            $followedBrands = UserCacheManager::getInstance()->getUserObject($user, UserCacheManager::USER_CACHE_KEY_BRAND_FOLLOWINGS);
            if (!$followedBrands) {
                $followedBrands = $em->getRepository('AdEntifyCoreBundle:User')->getFollowedBrandsIds($user);
                UserCacheManager::getInstance()->setUserObject($user, UserCacheManager::USER_CACHE_KEY_BRAND_FOLLOWINGS, $followedBrands, UserCacheManager::USER_CACHE_TTL_BRAND_FOLLOWINGS);
            }
        }

        $sql = 'SELECT photo, tag FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.tags tag WITH (tag.visible = true AND tag.deletedAt IS NULL
                  AND tag.censored = false AND tag.validationStatus != :denied)
                LEFT JOIN photo.owner owner LEFT JOIN photo.categories category LEFT JOIN tag.brand brand
                WHERE category.id IN (:categories) AND photo.id != :photoId AND photo.status = :status AND photo.deletedAt IS NULL
                    AND (photo.owner = :currentUserId OR photo.visibilityScope = :visibilityScope OR (owner.facebookId IS NOT NULL AND owner.facebookId IN (:facebookFriendsIds))
                    OR owner.id IN (:followings) OR brand.id IN (:followedBrands)) ORDER BY photo.createdAt DESC';

        $query = $em->createQuery($sql)->setParameters(array(
                'categories' => $categoriesId,
                ':status' => Photo::STATUS_READY,
                ':visibilityScope' => Photo::SCOPE_PUBLIC,
                ':currentUserId' => $user ? $user->getId() : 0,
                ':facebookFriendsIds' => $facebookFriendsIds,
                ':followings' => $followings,
                ':photoId' => $id,
                ':denied' => Tag::VALIDATION_DENIED,
                ':followedBrands' => $followedBrands
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
     *      200="Returned if the photo is created",
     *      401="Returned when authentication is required",
     *  },
     *  section="Photo"
     * )
     *
     * @View(serializerGroups={"details"})
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

                if (isset($_FILES['file'])) {
                    $uploadedFile = $_FILES['file'];
                    $user = $this->container->get('security.context')->getToken()->getUser();
                    $path = FileTools::getUserPhotosPath($user);
                    $filename = uniqid().$uploadedFile['name'];
                    $file = $this->getRequest()->files->get('file');

                    $url = $this->get('adentify_storage.file_manager')->upload($file, $path, $filename);
                    if ($url) {
                        $thumb = new Thumb();
                        $thumb->setOriginalPath($url);
                        $thumb->configure($photo);
                        $thumbs = $this->container->get('ad_entify_core.thumb')->generateUserPhotoThumb($thumb, $user, $filename);

                        // Add original
                        $originalImageSize = getimagesize($url);
                        $thumbs['original'] = array(
                            'filename' => $url,
                            'width' => $originalImageSize[0],
                            'height' => $originalImageSize[1],
                        );

                        $photo->fillThumbs($thumbs);
                    } else {
                        throw new HttpException(500, 'Can\'t upload photo.');
                    }
                }

                // Get current user
                $user = $this->container->get('security.context')->getToken()->getUser();

                $photo->setOwner($user)->setStatus(Photo::STATUS_READY);

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
     *  section="Photo",
     *  parameters={
     *   {"name"="id", "dataType"="integer", "required"=true, "description"="photo ID"}
     *  }
     * )
     *
     * @View(serializerGroups={"details"})
     */
    public function putAction($id, Request $request)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $em = $this->getDoctrine()->getManager();
            $photo = $em->getRepository('AdEntifyCoreBundle:Photo')->find($id);
            $user = $this->container->get('security.context')->getToken()->getUser();
            if ($photo->getOwner()->getId() == $user->getId()) {
                $formPhoto = new Photo();
                $form = $this->getForm($formPhoto);
                $form->bind($request);
                if ($form->isValid()) {
                    $photo->setCaption($formPhoto->getCaption());
                    if ($formPhoto->getCategories()) {
                        $photo->setCategories($formPhoto->getCategories());
                    }
                    if (array_key_exists('hashtags', $request->request->get('photo'))) {
                        $photo->setHashtags($photo->getHashtags()->clear());
                        $hashtagRepository = $em->getRepository('AdEntifyCoreBundle:Hashtag');
                        $newPhoto = $request->request->get('photo');
                        foreach (array_unique($newPhoto['hashtags']) as $hashtagName) {
                            if (is_numeric($hashtagName)) {
                                $hashtag = $hashtagRepository->find($hashtagName);
                                if ($hashtag) {
                                    $photo->addHashtag($hashtag);
                                }
                            } else {
                                $hashtag = $hashtagRepository->createIfNotExist($hashtagName);
                                if ($hashtag) {
                                    $found = false;
                                    if ($formPhoto->getHashtags()) {
                                        foreach($formPhoto->getHashtags() as $ht) {
                                            if ($ht->getId() == $hashtag->getId()) {
                                                $found = true;
                                                break;
                                            }
                                        }
                                    }
                                    if (!$found)
                                        $photo->addHashtag($hashtag);
                                }
                            }
                        }
                    }

                    $em->merge($photo);
                    $em->flush();
                    return $photo;
                } else {
                    return $form;
                }
            } else
                throw new HttpException(403, 'You are not authorized to edit this photo');
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * Delete a photo
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Delete a Photo",
     *  statusCodes={
     *      200="Returned if the photo is deleted",
     *      401="Returned when authentication is required",
     *  },
     *  section="Photo",
     *  parameters={
     *   {"name"="id", "dataType"="integer", "required"=true, "description"="photo ID"}
     *  }
     * )
     *
     * @View(serializerGroups={"details"})
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

                $em->getRepository('AdEntifyCoreBundle:Photo')->deleteLinkedData($photo);

                $em->merge($photo);
                $em->flush();
                return array('deleted' => true);
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
     * @ApiDoc(
     *  resource=true,
     *  description="Get all validated tags by photo ID",
     *  output="AdEntify\CoreBundle\Entity\Tag",
     *  statusCodes={
     *      401="Returned when authentication is required",
     *  },
     *  section="Photo",
     *  parameters={
     *   {"name"="id", "dataType"="integer", "required"=true, "description"="photo ID"}
     *  }
     * )
     *
     * @View(serializerGroups={"details"})
     * @param $id
     * @return ArrayCollection|null
     */
    public function getTagsAction($id)
    {
        return $this->getDoctrine()->getManager()->createQuery('SELECT tag FROM AdEntify\CoreBundle\Entity\Tag tag
                LEFT JOIN tag.photo photo WHERE photo.id = :id AND tag.visible = TRUE AND tag.deletedAt IS NULL
                  AND tag.censored = FALSE AND tag.validationStatus != :denied')
            ->setParameters(array(
                ':id' => $id,
                ':denied' => Tag::VALIDATION_DENIED
            ))
            ->getResult();
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get unvalidated tags by photo ID",
     *  output="AdEntify\CoreBundle\Entity\Tag",
     *  statusCodes={
     *      401="Returned when authentication is required",
     *  },
     *  section="Photo",
     *  parameters={
     *   {"name"="id", "dataType"="integer", "required"=true, "description"="photo ID"}
     *  }
     * )
     *
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
     * @ApiDoc(
     *  resource=true,
     *  description="Get all hashtags by photo ID",
     *  output="AdEntify\CoreBundle\Entity\Hashtag",
     *  statusCodes={
     *      401="Returned when authentication is required",
     *  },
     *  section="Photo",
     *  parameters={
     *   {"name"="id", "dataType"="integer", "required"=true, "description"="photo ID"}
     *  }
     * )
     *
     * @param $id
     *
     * @View(serializerGroups={"list"})
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
     * @ApiDoc(
     *  resource=true,
     *  description="Get all comments by photo ID",
     *  output="AdEntify\CoreBundle\Entity\Comment",
     *  statusCodes={
     *      401="Returned when authentication is required",
     *  },
     *  section="Photo",
     *  parameters={
     *   {"name"="id", "dataType"="integer", "required"=true, "description"="photo ID"}
     *  }
     * )
     *
     * GET all comments by photo ID
     *
     * @View(serializerGroups={"list"})
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
     * @ApiDoc(
     *  resource=true,
     *  description="Get all likers of a photo ID",
     *  output="AdEntify\CoreBundle\Entity\User",
     *  statusCodes={
     *      401="Returned when authentication is required",
     *  },
     *  section="Photo",
     *  parameters={
     *   {"name"="id", "dataType"="integer", "required"=true, "description"="photo ID"}
     *  }
     * )
     *
     * GET all likers by photo ID
     *
     * @View(serializerGroups={"list"})
     *
     * @param $id
     * @return ArrayCollection|null
     */
    public function getLikersAction($id)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $array = $this->getDoctrine()->getManager()->createQuery('SELECT user, (SELECT COUNT(u.id) FROM AdEntifyCoreBundle:User u
                LEFT JOIN u.followings following WHERE u.id = :currentUserId AND following.id = user.id) as followed FROM AdEntifyCoreBundle:User user
            LEFT JOIN user.likes l WHERE l.photo = :photoId AND l.deleted_at IS NULL')
                ->setParameters(array(
                    'photoId' => $id,
                    'currentUserId' => $this->container->get('security.context')->getToken()->getUser()->getId()
                ))->getResult();

            $likers = array();
            foreach ($array as $entry) {
                $liker = $entry[0];
                $liker->setFollowed($entry['followed'] > 0 ? true : false);
                $likers[] = $liker;
            }
            return $likers;
        } else {
            return $this->getDoctrine()->getManager()->createQuery('SELECT user FROM AdEntifyCoreBundle:User user LEFT JOIN user.likes l WHERE l.photo = :photoId AND l.deleted_at IS NULL')
                ->setParameters(array(
                    'photoId' => $id
                ))->getResult();
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get all categories of a photo by photo ID",
     *  output="AdEntify\CoreBundle\Entity\Category",
     *  statusCodes={
     *      401="Returned when authentication is required",
     *  },
     *  section="Photo",
     *  parameters={
     *   {"name"="id", "dataType"="integer", "required"=true, "description"="photo ID"},
     *   {"name"="localte", "dataType"="string", "required"=false, "description"="locale (en or fr)"}
     *  }
     * )
     *
     * GET all categories by photo ID
     *
     * @View(serializerGroups={"list"})
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
     * @View(serializerGroups={"list"})
     *
     * @param $id
     * @return ArrayCollection|null
     */
    public function getLikesAction($id)
    {
	return $this->getDoctrine()->getManager()->createQuery('SELECT l FROM AdEntifyCoreBundle:Like l
	    WHERE l.photo = :photoId AND l.deleted_at IS NULL')
	    ->setParameter(':photoId', $id)
	    ->getResult();
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Check if current user liked the photo",
     *  output="boolean",
     *  statusCodes={
     *      200="Return true if liked, false if not",
     *      401="Returned when authentication is required",
     *  },
     *  section="Photo",
     *  parameters={
     *   {"name"="id", "dataType"="integer", "required"=true, "description"="photo ID"}
     *  }
     * )
     *
     * @View(serializerGroups={"details"})
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
              WHERE l.photo = :photoId AND l.liker = :userId AND l.deleted_at IS NULL')
                ->setParameters(array(
                    'photoId' => $id,
                    'userId' => $user->getId()
                ))
                ->getSingleScalarResult();

            return array('liked' => $count > 0);
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Check if current user favorited the photo",
     *  output="boolean",
     *  statusCodes={
     *      200="Return true if favorited, false if not",
     *      401="Returned when authentication is required",
     *  },
     *  section="Photo",
     *  parameters={
     *   {"name"="id", "dataType"="integer", "required"=true, "description"="photo ID"}
     *  }
     * )
     *
     * @View(serializerGroups={"details"})
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

            return $count > 0 ? array('favorites' => true) : array('favorites' => false);
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get user photos of current logged in user",
     *  output="AdEntify\CoreBundle\Entity\Photo",
     *  statusCodes={
     *      401="Returned when authentication is required",
     *  },
     *  section="Photo"
     * )
     *
     * @View(serializerGroups={"list"})
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
     * @ApiDoc(
     *  resource=true,
     *  description="Add a photo to favorite of current logged in user",
     *  statusCodes={
     *      200="Returned if successfull",
     *      401="Returned when authentication is required",
     *  },
     *  section="Photo",
     *  parameters={
     *   {"name"="photoId", "dataType"="integer", "required"=true, "description"="photo ID"}
     *  }
     * )
     *
     * @View(serializerGroups={"details"})
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
                        if ($favoritePhoto->getId() == $photo->getId()) {
                            $found = true;
                            break;
                        }
                    }

                    if (!$found) {
                        $favorites = true;
                        // Add favorite
                        $user->addFavoritePhoto($photo);

                        // FAVORITE Action & notification
                        $sendNotification = $user->getId() != $photo->getOwner()->getId();
                        $em->getRepository('AdEntifyCoreBundle:Action')->createAction(Action::TYPE_PHOTO_FAVORITE,
                            $user, $photo->getOwner(), array($photo), Action::getVisibilityWithPhotoVisibility($photo->getVisibilityScope()), $photo->getId(),
                            $em->getClassMetadata(get_class($photo))->getName(), $sendNotification, 'photoFav');
                    } else {
                        $user->removeFavoritePhoto($photo);
                        $favorites = false;
                    }

                    $em->merge($user);
                    $em->flush();
                    return array(
                        'favorites' => $favorites
                    );
                }
            }
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
}