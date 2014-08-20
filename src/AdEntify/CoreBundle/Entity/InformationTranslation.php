<?php

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractPersonalTranslation;

/**
 * @ORM\Table(name="informations_translations", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="lookup_unique_idx", columns={"locale", "object_id", "field"})
 * })
 * @ORM\Entity
 */
class InformationTranslation extends AbstractPersonalTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity="Information", inversedBy="translations")
     * @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $object;
}