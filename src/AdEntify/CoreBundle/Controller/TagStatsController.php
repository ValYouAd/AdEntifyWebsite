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
     * @View()
     */
    public function postAction(Request $request)
    {
        if ($request->request->has('tagId') && $request->request->has('statType')
            && is_numeric($request->request->get('tagId'))) {
            $em = $this->getDoctrine()->getManager();
            $tagStats = $em->createQuery('SELECT tagStats FROM AdEntify\CoreBundle\Entity\TagStats tagStats
              LEFT JOIN tagStats.tag tag WHERE tagStats.ipAddress = :ipAddress AND tagStats.type = :statType
              AND tag.id = :tagId')
                ->setParameters(array(
                    ':ipAddress' => $request->getClientIp(),
                    ':tagId' => $request->request->get('tagId'),
                    ':statType' => $request->request->get('statType')
                ))
                ->SetMaxResults(1)
                ->getOneOrNullResult();

            if (!$tagStats) {
                $tag = $em->getRepository('AdEntifyCoreBundle:Tag')->find($request->request->get('tagId'));
                if ($tag) {
                    $tagStats = new TagStats();
                    $tagStats->setIpAddress($request->getClientIp())->setTag($tag)
                        ->setType($request->request->get('statType'));

                    // Set user if loggedin
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