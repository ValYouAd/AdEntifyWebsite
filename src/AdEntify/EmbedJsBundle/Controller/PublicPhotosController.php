<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 08/08/2013
 * Time: 17:18
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\EmbedJsBundle\Controller;

use AdEntify\CoreBundle\Entity\Tag;
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
use AdEntify\CoreBundle\Entity\Photo;

/**
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("public-api/v1")
 *
 * @RouteResource("Photo")
 */
class PublicPhotosController extends FOSRestController
{
    /**
     * @View()
     *
     * @param $id
     * @return JsonResponse
     */
    public function getAction($id)
    {
        $response = new JsonResponse();
        $serializer = $this->container->get('serializer');
        $tags = $this->getDoctrine()->getManager()->createQuery('SELECT photo, tag FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.tags tag
                WHERE photo.id = :id AND photo.status = :status AND photo.visibilityScope = :visibilityScope AND (tag IS NULL OR tag.visible = true
                AND tag.deletedAt IS NULL AND tag.censored = FALSE AND tag.waitingValidation = FALSE
                AND (tag.validationStatus = :none OR tag.validationStatus = :granted))')
            ->setParameters(array(
                ':id' => $id,
                ':status' => Photo::STATUS_READY,
                ':visibilityScope' => Photo::SCOPE_PUBLIC,
                ':none' => Tag::VALIDATION_NONE,
                ':granted' => Tag::VALIDATION_GRANTED
            ))
            ->getOneOrNullResult();
        $response->setJsonData($serializer->serialize($tags, 'json'));
        if ($this->getRequest()->getRequestFormat() && $this->getRequest()->query->get("callback")) {
            $response->setCallback($this->getRequest()->query->get("callback"));
        }
        return $response;
    }
}