<?php

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\Action;
use AdEntify\CoreBundle\Entity\Photo;
use AdEntify\CoreBundle\Form\BrandType;
use AdEntify\CoreBundle\Model\Thumb;
use AdEntify\CoreBundle\Util\FileTools;
use AdEntify\CoreBundle\Util\PaginationTools;
use AdEntify\CoreBundle\Util\UserCacheManager;
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

use AdEntify\CoreBundle\Entity\Information;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class InformationsController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("info")
 */
class InformationsController extends FOSRestController {

    /**
     * Get a collection of all informations
     *
     * @return ArrayCollection
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Get a collection of informations",
     *  output="AdEntify\CoreBundle\Entity\Information",
     *  section="Informations"
     * )
     *
     * @View()
     * @QueryParam(name="locale", default="en")
     */
    public function cgetAction($locale = 'en')
    {
        return $this->getDoctrine()->getManager()
            ->createQuery("SELECT info FROM AdEntify\CoreBundle\Entity\Information info")
            ->useQueryCache(false)
            ->useResultCache(true, null, 'infos'.$locale)
            ->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale)
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_FALLBACK, 1)
            ->getResult();
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get an information by ID",
     *  output="AdEntify\CoreBundle\Entity\Information",
     *  section="Informations",
     *  parameters={
     *      {"name"="id", "dataType"="integer", "required"=true, "description"="information id"}
     *  }
     * )
     *
     * @View()
     * @QueryParam(name="locale", default="en")
     * @return Information
     */
    public function getAction($id, $locale)
    {
        return $this->getDoctrine()->getManager()
            ->createQuery("SELECT info FROM AdEntify\CoreBundle\Entity\Information info WHERE info.id =:id")
            ->setParameter(':id', $id)
            ->useQueryCache(false)
            ->useResultCache(true, null, 'infos'.$locale)
            ->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale)
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_FALLBACK, 1)
            ->getOneOrNullResult();
    }
}