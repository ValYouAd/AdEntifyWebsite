<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 04/06/2013
 * Time: 19:06
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\TagStats;
use AdEntify\CoreBundle\Form\TagType;
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

use Nelmio\ApiDocBundle\Annotation\ApiDoc;

/**
 * Class TagsController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("TagStats")
 */
class TagStatsController extends FosRestController
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Post a tag stats. Two parameters : tagId and statType ('hover' or 'click')",
     *  output="AdEntify\CoreBundle\Entity\TagStats",
     *  statusCodes={
     *      200="Returned if the tag is created",
     *  },
     *  section="TagStats"
     * )
     *
     * @View(serializerGroups={"details"})
     */
    public function postAction(Request $request)
    {
        if ($request->request->has('tagId') && $request->request->has('statType')
            && is_numeric($request->request->get('tagId'))) {
            $em = $this->getDoctrine()->getManager();

            $qb = $em->getRepository('AdEntifyCoreBundle:TagStats')->createQueryBuilder('tagStats');
            $qb->leftJoin('tagStats.tag', 'tag')
                ->where('tag.id = :tagId')
                ->andWhere('tagStats.ipAddress = :ipAddress')
                ->andWhere('tagStats.type = :statType');

            $parameters = array(
                ':ipAddress' => $request->getClientIp(),
                ':tagId' => $request->request->get('tagId'),
                ':statType' => $request->request->get('statType')
            );

            if ($request->request->has('link')) {
                $qb->andWhere('tagStats.link = :link');
                $parameters['link'] = $request->request->get('link');
            }
            if ($request->request->has('platform')) {
                $qb->andWhere('tagStats.platform = :platform');
                $parameters['platform'] = $request->request->get('platform');
            }

            $tagStats = $qb->setMaxResults(1)->setParameters($parameters)->getQuery()->getOneOrNullResult();

            if (!$tagStats) {
                $tag = $em->getRepository('AdEntifyCoreBundle:Tag')->find($request->request->get('tagId'));
                if ($tag) {
                    $tagStats = new TagStats();
                    $tagStats->setIpAddress($request->getClientIp())->setTag($tag)
                        ->setType($request->request->get('statType'));
                    if ($request->request->has('platform'))
                        $tagStats->setPlatform($request->request->get('platform'));
                    if ($request->request->has('link'))
                        $tagStats->setLink($request->request->get('link'));

                    // Set user if logged in
                    $securityContext = $this->container->get('security.context');
                    if( $securityContext->isGranted('IS_AUTHENTICATED_REMEMBERED') ){
                        $tagStats->setUser($this->container->get('security.context')->getToken()->getUser());
                    }

                    $em->persist($tagStats);
                    $em->flush();
                    return $tagStats;
                }
            } else {
                return $tagStats;
            }
        }
    }
}