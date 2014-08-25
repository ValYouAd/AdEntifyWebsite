<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 18/04/2013
 * Time: 17:57
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\Person;
use AdEntify\CoreBundle\Entity\User;
use AdEntify\CoreBundle\Entity\Photo;
use AdEntify\CoreBundle\Form\PersonType;
use Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class PeopleController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("Person")
 */
class PeopleController extends FosRestController
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get all persons",
     *  output="AdEntify\CoreBundle\Entity\Person",
     *  section="Person"
     * )
     *
     * @View(serializerGroups={"list"})
     */
    public function cgetAction()
    {
        return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Person')->findAll();
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get a person by id",
     *  output="AdEntify\CoreBundle\Entity\Person",
     *  section="Person"
     * )
     *
     * @View(serializerGroups={"details"})
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

        $person = $em->getRepository('AdEntifyCoreBundle:Person')->find($id);
        if ($person) {
            $lastPhoto = $em->createQuery('SELECT photo
                                           FROM AdEntifyCoreBundle:Photo photo
                                           LEFT JOIN photo.tags tag INNER JOIN photo.owner owner LEFT JOIN tag.brand brand LEFT JOIN tag.person person
                                           WHERE person.id = :personId AND photo.status = :status AND photo.deletedAt IS NULL
                                              AND (photo.visibilityScope = :visibilityScope
                                                OR (owner.facebookId IS NOT NULL AND owner.facebookId IN (:facebookFriendsIds))
                                                OR owner.id IN (:followings)
                                                OR brand.id IN (:followedBrands))
                                           ORDER BY photo.id DESC')
                ->setParameters(array(
                    ':status' => Photo::STATUS_READY,
                    ':visibilityScope' => Photo::SCOPE_PUBLIC,
                    ':facebookFriendsIds' => $facebookFriendsIds,
                    ':followings' => $followings,
                    ':followedBrands' => $followedBrands,
                    ':personId' => $person->getId(),
                ))
                ->setMaxResults(1)
                ->getOneOrNullResult();

            $person->setLastPhoto($lastPhoto);
            return $person;
        } else
            throw new HttpException(404);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Post a Person",
     *  input="AdEntify\CoreBundle\Form\PersonType",
     *  output="AdEntify\CoreBundle\Entity\Person",
     *  section="Person"
     * )
     *
     * @View(serializerGroups={"details"})
     */
    public function postAction(Request $request)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            // Check if existing user exist with person facebookId
            if (!$request->request->has('person'))
                throw new HttpException(404);

            $em = $this->getDoctrine()->getManager();

            $personRequest = $request->request->get('person');
            $user = null;
            $person = null;
            if (array_key_exists('facebookId', $personRequest)) {
                $user = $em->getRepository('AdEntifyCoreBundle:User')->findOneBy(array(
                    'facebookId' => $personRequest['facebookId']
                ));
                $person = $em->getRepository('AdEntifyCoreBundle:Person')->findOneBy(array(
                    'facebookId' => $personRequest['facebookId']
                ));
            } else if (array_key_exists('id', $personRequest)) {
                $person = $em->getRepository('AdEntifyCoreBundle:Person')->find($personRequest['id']);
            }

            if ($person) {
                if (!$person->getUser() && $user)
                    $person->setUser($user);

                $em->merge($person);
                $em->flush();

                return $person;
            } else {
                $person = new Person();
                $form = $this->getForm($person);
                $form->bind($request);
                if ($form->isValid()) {
                    $em = $this->getDoctrine()->getManager();
                    if ($user) {
                        $person->setUser($user);
                    }
                    if (!$person->getGender()) {
                        $person->setGender(User::GENDER_UNKNOWN);
                    }
                    $em->persist($person);
                    $em->flush();
                    return $person;
                } else {
                    return $form;
                }
            }
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @param $query
     * @param int $limit
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Search a person with a query (keyword)",
     *  output="AdEntify\CoreBundle\Entity\Person",
     *  section="Person"
     * )
     *
     * @QueryParam(name="limit", default="10")
     * @View(serializerGroups={"list"})
     */
    public function getSearchAction($query, $limit)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $people = $this->getDoctrine()->getManager()->createQuery('SELECT p FROM AdEntifyCoreBundle:Person p
                WHERE p.name LIKE :query OR p.firstname LIKE :query OR p.lastname LIKE :query')
                ->setMaxResults($limit)
                ->setParameters(array(
                    'query' => '%'.$query.'%'
                ))
                ->getResult();

            return $people;
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * Get form for Perso
     *
     * @param null $person
     * @return mixed
     */
    protected function getForm($person = null)
    {
        return $this->createForm(new PersonType(), $person);
    }
}