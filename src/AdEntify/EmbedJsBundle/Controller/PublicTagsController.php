<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 26/07/2013
 * Time: 19:34
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\EmbedJsBundle\Controller;

use AdEntify\CoreBundle\Entity\TagStats;
use FOS\RestBundle\FOSRestBundle;
use Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection;

use AdEntify\CoreBundle\Util\JsonResponse;
use AdEntify\CoreBundle\Entity\Tag;

/**
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("public-api/v1")
 *
 * @RouteResource("Tag")
 */
class PublicTagsController extends FOSRestController
{
    /**
     * GET all tags by photo ID
     *
     * @param $id
     * @return ArrayCollection|null
     */
    public function cgetAction($id)
    {
        $response = new JsonResponse();
        $serializer = $this->container->get('serializer');
        $tags = $this->getDoctrine()->getManager()->createQuery('SELECT tag FROM AdEntify\CoreBundle\Entity\Tag tag
                LEFT JOIN tag.photo photo WHERE photo.id = :id AND tag.visible = TRUE AND tag.deletedAt IS NULL
                  AND tag.censored = FALSE AND tag.waitingValidation = FALSE')
            ->setParameters(array(
                ':id' => $id
            ))
            ->getResult();
        $response->setJsonData($serializer->serialize($tags, 'json'));
        if ($this->getRequest()->getRequestFormat() && $this->getRequest()->query->get("callback")) {
            $response->setCallback($this->getRequest()->query->get("callback"));
        }
        return $response;
    }

    /**
     * @View()
     */
    public function postStatAction(Request $request)
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