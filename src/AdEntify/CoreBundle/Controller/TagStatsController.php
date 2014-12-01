<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 04/06/2013
 * Time: 19:06
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\Analytic;
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
use Symfony\Component\HttpKernel\Exception\HttpException;

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

            $analytic = new Analytic();
            $analytic->setIpAddress($request->getClientIp())->setElement(Analytic::ELEMENT_TAG);
            if ($this->getUser())
                $analytic->setUser($this->getUser());

            // Action
            switch ($request->request->get('statType')) {
                case TagStats::TYPE_CLICK:
                    $analytic->setAction(Analytic::ACTION_CLICK);
                    break;
                case TagStats::TYPE_HOVER:
                    $analytic->setAction(Analytic::ACTION_HOVER);
                    break;
            }

            // Platform
            if ($request->request->has('platform')) {
                $analytic->setPlatform($request->request->get('platform'));
            } else {
                $analytic->setPlatform('adentify');
            }

            if (!$em->getRepository('AdEntifyCoreBundle:Analytic')->isAlreadyTracked($analytic)) {
                $tag = $em->getRepository('AdEntifyCoreBundle:Tag')->find($request->request->get('tagId'));
                if ($tag) {
                    $analytic->setTag($tag);
                    $em->persist($analytic);
                    $em->flush();

                    return $analytic;
                } else
                    throw new HttpException('404', 'Tag not found');
            } else
                throw new HttpException('403', 'Already tracked');
        }
    }
}