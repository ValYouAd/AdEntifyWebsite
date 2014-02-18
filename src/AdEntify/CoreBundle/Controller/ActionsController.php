<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 25/10/2013
 * Time: 17:39
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\Photo;
use AdEntify\CoreBundle\Util\PaginationTools;
use AdEntify\CoreBundle\Util\UserCacheManager;
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

use AdEntify\CoreBundle\Entity\Action;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class ActionsController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("Action")
 */
class ActionsController extends FosRestController
{
    /**
     * Get actions for logged user
     *
     * @View(serializerGroups={"slight-list"})
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="20")
     */
    public function cgetAction($page, $limit)
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

        $query = $em->createQuery('SELECT action FROM AdEntify\CoreBundle\Entity\Action action
            LEFT JOIN action.target target LEFT JOIN action.author author LEFT JOIN action.brand brand
            WHERE author.id != :userId AND (author.id IN (:facebookFriendsIds) OR author.id IN (:followings) OR brand.id IN (:followedBrands))
            ORDER BY action.createdAt DESC')
            ->setParameters(array(
                'userId' => $user ? $user->getId() : 0,
                'facebookFriendsIds' => $facebookFriendsIds,
                'followings' => $followings,
                'followedBrands' => $followedBrands
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

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_actions');
        }

        return PaginationTools::getPaginationArray($actions, $pagination);
    }
} 