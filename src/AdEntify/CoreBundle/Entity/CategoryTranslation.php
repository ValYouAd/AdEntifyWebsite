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
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

/**
 * @ORM\Table(name="category_translations", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="lookup_unique_idx", columns={"locale", "object_id", "field"})
 * })
 * @ORM\Entity
 */
class CategoryTranslation extends AbstractPersonalTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity="Category", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}