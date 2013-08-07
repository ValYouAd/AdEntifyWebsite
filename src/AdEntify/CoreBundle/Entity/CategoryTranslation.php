<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 11/06/2013
 * Time: 17:54
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * @ORM\Table(name="category_translations", indexes={
 *      @ORM\Index(name="category_translation_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity(repositoryClass="Gedmo\Translatable\Entity\Repository\TranslationRepository")
 */
class CategoryTranslation extends AbstractTranslation { }