<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 08/01/2014
 * Time: 10:39
 */

namespace AdEntify\CoreBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

/**
 * @ORM\Table(name="product_type_translations", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="lookup_unique_idx", columns={"locale", "object_id", "field"})
 * })
 * @ORM\Entity
 */
class ProductTypeTranslation extends AbstractPersonalTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity="ProductType", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}