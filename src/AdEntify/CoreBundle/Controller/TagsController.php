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
use AdEntify\CoreBundle\Entity\Photo;
use AdEntify\CoreBundle\Entity\SearchHistory;
use AdEntify\CoreBundle\Form\TagType;
use AdEntify\CoreBundle\Util\PaginationTools;
use AdEntify\CoreBundle\Util\UserCacheManager;
use AdEntify\CoreBundle\Validator\AgeValidator;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection;

use AdEntify\CoreBundle\Entity\Tag;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class TagsController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("Tag")
 */
class TagsController extends FosRestController
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get a collection of tags",
     *  output="AdEntify\CoreBundle\Entity\Tag",
     *  section="Tag"
     * )
     *
     * @View()
     *
     * @return mixed
     */
    public function cgetAction()
    {
        return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Tag')->findAll();
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get a tag",
     *  output="AdEntify\CoreBundle\Entity\Tag",
     *  section="Tag"
     * )
     *
     * @View()
     *
     * @return Tag
     */
    public function getAction($id)
    {
        return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Tag')->find($id);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Delete a tag",
     *  statusCodes={
     *      204="Returned if the tag is deleted",
     *      403="Returned if the user is not authorized to delete the tag"
     *  },
     *  section="Tag"
     * )
     *
     * @View()
     *
     * @param $id
     */
    public function deleteAction($id)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $tag = $this->getAction($id);
            $user = $this->container->get('security.context')->getToken()->getUser();
            if ($user->getId() == $tag->getOwner()->getId()) {
                $em = $this->getDoctrine()->getManager();
                $tag->setDeletedAt(new \DateTime());
                $em->merge($tag);
                $em->flush();
            } else {
                throw new HttpException(403, 'You are not authorized to delete this tag');
            }
        } else {
            throw new HttpException(401);
        }
    }

    /**
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Post a tag",
     *  input="AdEntify\CoreBundle\Form\TagType",
     *  output="AdEntify\CoreBundle\Entity\Tag",
     *  statusCodes={
     *      200="Returned if the tag is created",
     *      403="Returned if the user is not authorized to add a tag to the photo"
     *  },
     *  section="Tag"
     * )
     *
     * @View()
     */
    public function postAction(Request $request)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $tag = new Tag();
            $form = $this->getForm($tag);
            $form->bind($request);
            if ($form->isValid()) {

                $tag->setCreatedAt(new \DateTime());
                // Get current user
                $user = $this->container->get('security.context')->getToken()->getUser();

                $ageObject = null;
                if ($tag->getProduct())
                    $ageObject = $tag->getProduct();
                if ($tag->getBrand())
                    $ageObject = $tag->getBrand();
                if ($ageObject) {
                    if (!AgeValidator::validateAge($ageObject, $user)) {
                        $form->addError(new FormError('error.tooYoung'));
                        return $form;
                    }
                }

                $em = $this->getDoctrine()->getManager();

                // Calculate tag points
                $this->get('ad_entify_core.points')->calculateTagPoints($tag);

                // Get friends list (id) array
                $facebookFriendsIds = UserCacheManager::getInstance()->getUserObject($user, UserCacheManager::USER_CACHE_KEY_FB_FRIENDS);
                if (!$facebookFriendsIds) {
                    $facebookFriendsIds = $em->getRepository('AdEntifyCoreBundle:User')->refreshFriends($user, $this->container->get('fos_facebook.api'));
                    UserCacheManager::getInstance()->setUserObject($user, UserCacheManager::USER_CACHE_KEY_FB_FRIENDS, $facebookFriendsIds, UserCacheManager::USER_CACHE_TTL_FB_FRIENDS);
                }

                // Check if user is the owner of the photo
                $photo = $em->getRepository('AdEntifyCoreBundle:Photo')->find($tag->getPhoto()->getId());
                if ($photo->getOwner()->getId() !== $user->getId()) {
                    // Check if owner is a friend or photo is public
                    if ($photo->getVisibilityScope() == Photo::SCOPE_PUBLIC || $photo->getOwner()->getFacebookId() && in_array($photo->getOwner()->getFacebookId(), $facebookFriendsIds)) {
                        $tag->setWaitingValidation(true)->setValidationStatus(Tag::VALIDATION_WAITING);

                        // Create a new notification
                        $notification = new Notification();
                        $notification->setType(Action::TYPE_PHOTO_TAG)->setObjectId($photo->getId())->addPhoto($photo)
                            ->setObjectType(get_class($photo))->setOwner($photo->getOwner())
                            ->setAuthor($user)->setMessage('notification.friendTagPhoto');
                        $em->persist($notification);
                    } else {
                        throw new HttpException(403, 'You can\t add a tag to this photo');
                    }
                } else {
                    // TAG Action
                    $em->getRepository('AdEntifyCoreBundle:Action')->createAction(Action::TYPE_PHOTO_TAG,
                        $user, $photo->getOwner(), array($photo), Action::getVisibilityWithPhotoVisibility($photo->getVisibilityScope()), $photo->getId(),
                        get_class($photo), false, 'tagPhoto');

                    $this->get('ad_entify_core.points')->calculateUserPoints($user, $tag);
                }

                $tag->setOwner($user);

                if ($tag->getLink()) {
                    $shortUrl = $em->getRepository('AdEntifyCoreBundle:ShortUrl')->createShortUrl($tag->getLink());
                    if ($shortUrl)
                        $tag->setShortUrl($shortUrl)->setLink($this->generateUrl('redirect_url', array(
                            'id' => $shortUrl->getBase62Id()
                        )));
                }

                $em->persist($tag);
                $em->flush();

                $this->container->get('ad_entify_core.tagRevenue')->calculateRevenueForBrandTagging($tag, $request);

                return $tag;
            } else {
                return $form;
            }
        } else {
            throw new HttpException(401);
        }

    }

    /**
     * @View()
     *
     * @param $id
     * @param Request $request
     */
    public function postValidationStatusAction($id, Request $request)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            // Get current user
            $user = $this->container->get('security.context')->getToken()->getUser();

            $tag = $this->getAction($id);
            if ($tag->getPhoto()->getOwner()->getId() != $user->getId()) {
                throw new HttpException(403, 'Access forbidden');
            }

            if ($request->request->has('waiting_validation')) {
                $em = $this->getDoctrine()->getManager();
                $tag->setWaitingValidation(false);

                $status = $request->request->get('waiting_validation');
                if ($status == Tag::VALIDATION_GRANTED) {
                    $tag->setValidationStatus(Tag::VALIDATION_GRANTED);
                    $this->get('ad_entify_core.points')->calculateUserPoints($user, $tag);
                    $em->merge($tag);
                    $em->flush();
                } else if ($status == Tag::VALIDATION_DENIED) {
                    $tag->setValidationStatus(Tag::VALIDATION_DENIED);
                    $em->merge($tag);
                    $em->flush();
                }

                $this->container->get('ad_entify_core.tagRevenue')->calculateRevenueForBrandTagging($tag, $request);

                return $tag->getValidationStatus();
            } else
                throw new NotFoundHttpException('Tag not found');
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @View()
     *
     * @param $id
     * @return Venue|null
     */
    public function getVenue($id)
    {
        $tag = $this->getAction($id);
        if (!$tag)
            return null;
        return $tag->getVenue();
    }

    /**
     * @View()
     *
     * @param $id
     * @return Product|null
     */
    public function getProduct($id)
    {
        $tag = $this->getAction($id);
        if (!$tag)
            return null;
        return $tag->getProduct();
    }

    /**
     * @View()
     *
     * @param $id
     * @return Person|null
     */
    public function getPerson($id)
    {
        $tag = $this->getAction($id);
        if (!$tag)
            return null;
        return $tag->getPerson();
    }

    /**
     * Get form for Tag
     *
     * @param null $tag
     * @return mixed
     */
    protected function getForm($tag = null)
    {
        return $this->createForm(new TagType(), $tag);
    }
}