<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/06/2013
 * Time: 11:46
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Form\PhotoType;
use Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection;

use AdEntify\CoreBundle\Entity\Category;
use AdEntify\CoreBundle\Entity\Photo;

/**
 * Class CategoriesController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("Category")
 */
class CategoriesController extends FosRestController
{
    /**
     * @View()
     * @QueryParam(name="locale", default="fr")
     * @return Category
     */
    public function getAction($slug, $locale = 'fr')
    {
        return $this->getDoctrine()->getManager()
            ->createQuery("SELECT category FROM AdEntify\CoreBundle\Entity\Category category WHERE category.slug = :slug")
            ->setParameter(':slug', $slug)
            ->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale)
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_FALLBACK, 1)
            ->getOneOrNullResult();
    }

    /**
     * @View()
     * @QueryParam(name="locale", default="fr")
     */
    public function cgetAction($locale = 'fr')
    {
        return $this->getDoctrine()->getManager()
            ->createQuery("SELECT category FROM AdEntify\CoreBundle\Entity\Category category")
            ->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, $locale)
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_FALLBACK, 1)
            ->getResult();
    }

    /**
     * @View()
     * @QueryParam(name="tagged", default="true")
     */
    public function getPhotosAction($slug, $tagged)
    {
        return $this->getDoctrine()->getManager()->createQuery('SELECT photo, tag FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.tags tag LEFT JOIN photo.categories category WHERE photo.status = :status
                AND photo.visibilityScope = :visibilityScope AND '.($tagged == 'true' ? 'photo.tagsCount > 0 AND tag.visible = true
                AND tag.deleted_at IS NULL AND tag.censored = FALSE AND tag.waitingValidation = FALSE' : 'photo.tagsCount = 0').
        ' AND category.slug = :slug ORDER BY photo.createdAt DESC')
            ->setParameters(array(
                ':status' => Photo::STATUS_READY,
                ':visibilityScope' => Photo::SCOPE_PUBLIC,
                ':slug' => $slug
            ))
            ->getResult();
    }
}