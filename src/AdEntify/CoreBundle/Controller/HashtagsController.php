<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 29/10/2013
 * Time: 16:42
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Util\CommonTools;
use AdEntify\CoreBundle\Util\PaginationTools;
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

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class HashtagsController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("Hashtag")
 */
class HashtagsController extends FosRestController
{
    /**
     * Get hashtags
     *
     * @View()
     * @QueryParam(name="page", requirements="\d+", default="1")
     * @QueryParam(name="limit", requirements="\d+", default="30")
     */
    public function cgetAction($page, $limit)
    {
        $query = $this->getDoctrine()->getManager()->createQuery('SELECT hashtag FROM AdEntify\CoreBundle\Entity\Hashtag hashtag ORDER BY hashtag.usedCount DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);

        $paginator = new Paginator($query, $fetchJoinCollection = true);
        $count = count($paginator);

        $hashtags = null;
        $pagination = null;
        if ($count > 0) {
            $hashtags = array();
            foreach($paginator as $action) $hashtags[] = $action;
            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_hashtags');
        }

        return PaginationTools::getPaginationArray($hashtags, $pagination);
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

        $hashtags = CommonTools::extractHashtags($query, false, true);
        if (count($hashtags) == 0)
            $hashtags = explode(" ", $query);

        $query = $em->createQuery('SELECT hashtag FROM AdEntify\CoreBundle\Entity\Hashtag hashtag
        WHERE (REGEXP(hashtag.name, :hashtags) > 0)')
            ->setParameters(array(
                ':hashtags' => implode('|', $hashtags)
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

            $pagination = PaginationTools::getNextPrevPagination($count, $page, $limit, $this, 'api_v1_get_hashtag_search', array(
                'query' => $query
            ));
        }

        return PaginationTools::getPaginationArray($results, $pagination);
    }
} 