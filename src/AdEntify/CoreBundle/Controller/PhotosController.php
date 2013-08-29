<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 18/04/2013
 * Time: 18:46
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\Notification;
use AdEntify\CoreBundle\Entity\Tag;
use AdEntify\CoreBundle\Form\PhotoType;
use AdEntify\CoreBundle\Util\PaginationTools;
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
     * GET all photos public and ready
     *
     * @View()
     * @QueryParam(name="tagged", default="true")
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="30")
     */
    public function cgetAction($tagged, $page, $limit)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        // Get friends list (id) array
        $facebookFriendsIds = $em->getRepository('AdEntifyCoreBundle:User')->refreshFriends($user, $this->container->get('fos_facebook.api'));
        // Get followings ids
        $followings = $user->getFollowingsIds();

        $parameters = $tagged == 'true' ? array(
            ':status' => Photo::STATUS_READY,
            ':visibilityScope' => Photo::SCOPE_PUBLIC,
            ':facebookFriendsIds' => $facebookFriendsIds,
            ':followings' => $followings,
            ':none' => Tag::VALIDATION_NONE,
            ':granted' => Tag::VALIDATION_GRANTED
        ) : array(
            ':status' => Photo::STATUS_READY,
            ':visibilityScope' => Photo::SCOPE_PUBLIC,
            ':facebookFriendsIds' => $facebookFriendsIds,
            ':followings' => $followings
        );

        $count = $em->createQuery('SELECT COUNT(photo.id) FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.tags tag LEFT JOIN photo.owner owner
                WHERE photo.status = :status AND (photo.visibilityScope = :visibilityScope
                OR (owner.facebookId IS NOT NULL AND owner.facebookId IN (:facebookFriendsIds)) OR owner.id IN (:followings))
                AND '.($tagged == 'true' ? 'photo.tagsCount > 0 AND tag.visible = true AND tag.deleted_at IS NULL
                AND tag.censored = FALSE AND tag.waitingValidation = FALSE AND (tag.validationStatus = :none OR tag.validationStatus = :granted)' : 'photo.tagsCount = 0'))
            ->setParameters($parameters)
            ->getSingleScalarResult();

        $photos = null;
        $pagination = null;
        if ($count > 0) {
            $photos = $em->createQuery('SELECT photo FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.tags tag LEFT JOIN photo.owner owner
                WHERE photo.status = :status AND (photo.visibilityScope = :visibilityScope
                OR (owner.facebookId IS NOT NULL AND owner.facebookId IN (:facebookFriendsIds)) OR owner.id IN (:followings))
                AND '.($tagged == 'true' ? 'photo.tagsCount > 0 AND tag.visible = true AND tag.deleted_at IS NULL
                AND tag.censored = FALSE AND tag.waitingValidation = FALSE AND (tag.validationStatus = :none OR tag.validationStatus = :granted)' : 'photo.tagsCount = 0').
            ' ORDER BY photo.createdAt DESC')
                ->setParameters($parameters)
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getResult();

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_photos', array(
                'tagged' => $tagged
            ));
        }

        return PaginationTools::getPaginationArray($photos, $pagination);
    }

    /**
     * GET Photo by ID
     *
     * @View()
     *
     * @return Photo
     */
    public function getAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();
        // Get friends list (id) array
        $facebookFriendsIds = $em->getRepository('AdEntifyCoreBundle:User')->refreshFriends($user, $this->container->get('fos_facebook.api'));
        // Get followings ids
        $followings = $user->getFollowingsIds();

        $photo = $em->createQuery('SELECT photo, tag FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.owner owner LEFT JOIN photo.tags tag
                WHERE photo.id = :id AND photo.status = :status AND (tag IS NULL OR tag.visible = true
                AND tag.deleted_at IS NULL AND tag.censored = FALSE AND tag.waitingValidation = FALSE
                AND (tag.validationStatus = :none OR tag.validationStatus = :granted))
                AND (photo.visibilityScope = :visibilityScope OR (owner.facebookId IS NOT NULL
                AND owner.facebookId IN (:facebookFriendsIds)) OR owner.id IN (:followings))
                ORDER BY photo.createdAt DESC')
            ->setParameters(array(
                ':status' => Photo::STATUS_READY,
                ':visibilityScope' => Photo::SCOPE_PUBLIC,
                ':id' => $id,
                ':facebookFriendsIds' => $facebookFriendsIds,
                ':followings' => $followings,
                ':none' => Tag::VALIDATION_NONE,
                ':granted' => Tag::VALIDATION_GRANTED
            ))
            ->getOneOrNullResult();

        return $photo;
    }

    /**
     * @View()
     */
    public function putAction($id, Request $request)
    {
        $photo = $this->getAction($id);
        if ($photo) {
            $user = $this->container->get('security.context')->getToken()->getUser();
            if ($photo->getOwner()->getId() == $user->getId()) {
                $form = $this->getForm($photo);
                $form->bind($request);
                if ($form->isValid()) {
                    $em = $this->getDoctrine()->getManager();
                    $em->merge($photo);
                    $em->flush();
                } else {
                    $form->getErrorsAsString();
                }
            } else
                throw new ForbiddenHttpException();
        } else
            throw new HttpNotFoundException();
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
        $photo = $this->getAction($id);
        $user = $this->container->get('security.context')->getToken()->getUser();
        // Check if current user is the owner oh the photo and that no tags are link to the photo
        if ($user->getId() == $photo->getOwner()->getId() && count($photo->getTags()) == 0) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($photo);
            $em->flush();
        } else {
            throw new HttpException(403, 'You are not authorized to delete this tag');
        }
    }

    /**
     * GET all tags by photo ID
     *
     * @View()
     * @param $id
     * @return ArrayCollection|null
     */
    public function getTagsAction($id)
    {
        return $this->getDoctrine()->getManager()->createQuery('SELECT tag FROM AdEntify\CoreBundle\Entity\Tag tag
                LEFT JOIN tag.photo photo WHERE photo.id = :id AND tag.visible = TRUE AND tag.deleted_at IS NULL
                  AND tag.censored = FALSE AND tag.waitingValidation = FALSE AND (tag.validationStatus = :none OR tag.validationStatus = :granted)')
            ->setParameters(array(
                ':id' => $id,
                ':none' => Tag::VALIDATION_NONE,
                ':granted' => Tag::VALIDATION_GRANTED
            ))
            ->getResult();
    }

    /**
     * @View()
     *
     * @param $id
     */
    public function getWaitingTagsAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        return $em->createQuery('SELECT tag FROM AdEntify\CoreBundle\Entity\Tag tag
                LEFT JOIN tag.photo photo LEFT JOIN photo.owner as owner
                WHERE photo.id = :id and owner.id = :userId AND tag.visible = TRUE AND tag.deleted_at IS NULL
                AND tag.censored = FALSE AND tag.waitingValidation = TRUE and tag.validationStatus = :validationStatus')
            ->setParameters(array(
                ':id' => $id,
                ':validationStatus' => Tag::VALIDATION_WAITING,
                ':userId' => $user->getId()
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

        $photo = $this->getAction($id);
        if (!$photo)
            return null;
        return $photo->getComments();
    }

    /**
     * GET all categories by photo ID
     *
     * @View()
     * @QueryParam(name="locale", default="fr")
     *
     * @param $id
     * @return ArrayCollection|null
     */
    public function getCategoriesAction($id, $locale = 'fr')
    {
        return $this->getDoctrine()->getManager()
            ->createQuery("SELECT category FROM AdEntify\CoreBundle\Entity\Category category
                LEFT JOIN category.photos photo WHERE photo.id = :id AND category.visible = 1")
            ->setParameter('id', $id)
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
     * @QueryParam(name="tagged", default="true")
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="20")
     *
     * @param $id
     * @return ArrayCollection|null
     */
    public function getUserPhotosAction($tagged, $page = 1, $limit = 20)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $count = $em->createQuery('SELECT COUNT(photo.id) FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.tags tag
                WHERE photo.owner = :userId AND photo.status = :status AND '.($tagged == 'true' ? 'photo.tagsCount > 0 AND tag.visible = true
                AND tag.deleted_at IS NULL AND tag.censored = FALSE AND tag.waitingValidation = FALSE' : 'photo.tagsCount = 0'))
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
                WHERE photo.owner = :userId AND photo.status = :status AND '.($tagged == 'true' ? 'photo.tagsCount > 0 AND tag.visible = true
                AND tag.deleted_at IS NULL AND tag.censored = FALSE AND tag.waitingValidation = FALSE' : 'photo.tagsCount = 0').
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
        if ($request->request->has('photoId') && is_numeric($request->request->get('photoId'))) {
            $photo = $this->getAction($request->request->get('photoId'));
            if ($photo) {
                $user = $this->container->get('security.context')->getToken()->getUser();
                $found = false;
                foreach($user->getFavoritePhotos() as $favoritePhoto) {
                    if ($favoritePhoto->getId() == $photo->getId())
                        $found = true; break;
                }
                if (!$found) {
                    $em = $this->getDoctrine()->getManager();

                    // Add favorite
                    $user->addFavoritePhoto($photo);

                    // Notification
                    $notification = new Notification();
                    $notification->setType(Notification::TYPE_FAV_PHOTO)->setObjectId($photo->getId())
                        ->setObjectType(get_class($photo))->setOwner($photo->getOwner())->setMessage('notification.photoFav')
                        ->setAuthor($user)->setMessageOptions(json_encode(array(
                            'author' => $user->getFullname()
                        )));
                    $em->persist($notification);

                    $em->merge($user);
                    $em->flush();
                }
            }
        }
    }
}