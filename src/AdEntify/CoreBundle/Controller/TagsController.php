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
use AdEntify\CoreBundle\Entity\SearchHistory;
use AdEntify\CoreBundle\Form\TagType;
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
     * @View()
     */
    public function cgetAction()
    {
        return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Tag')->findAll();
    }

    /**
     * @View()
     *
     * @return Tag
     */
    public function getAction($id)
    {
        return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Tag')->find($id);
    }

    /**
     * @param $query
     * @param int $limit
     *
     * @QueryParam(name="query")
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="20")
     * @View()
     */
    public function getSearchAction($query, $page = 1, $limit = 20, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        // Historique de recherche
        $searchHistory = new SearchHistory();
        $searchHistory->setKeywords($query)->setUser($user)->setIpAddress($request->getClientIp());
        $em->persist($searchHistory);
        $em->flush();

        $count = $em->createQuery('SELECT COUNT(tag.id) FROM AdEntify\CoreBundle\Entity\Tag tag
                LEFT JOIN tag.venue venue LEFT JOIN tag.person person LEFT JOIN tag.product product LEFT JOIN product.brand brand
            WHERE tag.deleted_at IS NULL AND tag.censored = FALSE AND tag.waitingValidation = FALSE AND (tag.validationStatus = :none OR tag.validationStatus = :granted)
            LOWER(tag.title) LIKE LOWER(:query) OR LOWER(venue.name) LIKE LOWER(:query) OR LOWER(person.firstname)
            LIKE LOWER(:query) OR LOWER(person.lastname) LIKE LOWER(:query) OR LOWER(product.name) LIKE LOWER(:query)
            OR LOWER(brand.name) LIKE LOWER(:query)')
            ->setParameters(array(
                ':query' => '%'.$query.'%',
                ':none' => Tag::VALIDATION_NONE,
                ':granted' => Tag::VALIDATION_GRANTED
            ))
            ->getSingleScalarResult();

        $results = null;
        $pagination = null;
        if ($count > 0) {
            $results = $em->createQuery('SELECT tag FROM AdEntify\CoreBundle\Entity\Tag tag
            LEFT JOIN tag.venue venue LEFT JOIN tag.person person LEFT JOIN tag.product product LEFT JOIN product.brand brand
            WHERE tag.deleted_at IS NULL AND tag.censored = FALSE AND tag.waitingValidation = FALSE AND (tag.validationStatus = :none OR tag.validationStatus = :granted)
            LOWER(tag.title) LIKE LOWER(:query) OR LOWER(venue.name) LIKE LOWER(:query) OR LOWER(person.firstname)
            LIKE LOWER(:query) OR LOWER(person.lastname) LIKE LOWER(:query) OR LOWER(product.name) LIKE LOWER(:query)
            OR LOWER(brand.name) LIKE LOWER(:query)')
                ->setParameters(array(
                    ':query' => '%'.$query.'%',
                    ':none' => Tag::VALIDATION_NONE,
                    ':granted' => Tag::VALIDATION_GRANTED
                ))
                ->setFirstResult(($page - 1) * $limit)
                ->setMaxResults($limit)
                ->getResult();

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_tag_search', array(
                'query' => $query
            ));
        }

        return PaginationTools::getPaginationArray($results, $pagination);
    }

    /**
     * @View()
     */
    public function postAction(Request $request)
    {
        $tag = new Tag();
        $form = $this->getForm($tag);
        $form->bind($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // Get current user
            $user = $this->container->get('security.context')->getToken()->getUser();

            // Get friends list (id) array
            $facebookFriendsIds = $em->getRepository('AdEntifyCoreBundle:User')->refreshFriends($user, $this->container->get('fos_facebook.api'));

            // Check if user is the owner of the photo
            $photo = $em->getRepository('AdEntifyCoreBundle:Photo')->find($tag->getPhoto()->getId());
            if ($photo->getOwner()->getId() !== $user->getId()) {
                // Check if owner is a friend
                if ($photo->getOwner()->getFacebookId() && in_array($photo->getOwner()->getFacebookId(), $facebookFriendsIds)) {
                    $tag->setWaitingValidation(true);
                    $tag->setValidationStatus(Tag::VALIDATION_WAITING);

                    // Create a new notification
                    $notification = new Notification();
                    $notification->setType(Notification::TYPE_TAG_PHOTO)->setObjectId($photo->getId())
                        ->setObjectType(get_class($photo))->setOwner($photo->getOwner())
                        ->setMessageOptions(json_encode(array(
                            'author' => $user->getFullname()
                        )))->setMessage('notification.friendTagPhoto');
                    $em->persist($notification);
                } else {
                    throw new HttpException(403, 'You can\t add a tag to this photo');
                }
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
        } else {
            return $form;
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