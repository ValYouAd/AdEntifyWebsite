<?php

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * Information
 *
 * @ORM\Table(name="informations")
 * @ORM\Entity
 * @Gedmo\TranslationEntity(class="AdEntify\CoreBundle\Entity\InformationTranslation")
 */
class Information
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="info_key", type="string", length=255)
     *
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $infoKey;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="info", type="text")
     *
     * @Serializer\Groups({"details"})
     */
    private $info;

    /**
     * @ORM\OneToMany(
     *     targetEntity="AdEntify\CoreBundle\Entity\InformationTranslation",
     *  mappedBy="object",
     *  cascade={"persist", "remove"}
     * )
     *
     * @Serializer\Exclude
     */
    private $translations;

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set infoKey
     *
     * @param string $infoKey
     * @return Information
     */
    public function setInfoKey($infoKey)
    {
        $this->infoKey = $infoKey;
    
        return $this;
    }

    /**
     * Get infoKey
     *
     * @return string 
     */
    public function getInfoKey()
    {
        return $this->infoKey;
    }

    /**
     * Set info
     *
     * @param string $info
     * @return Information
     */
    public function setInfo($info)
    {
        $this->info = $info;
    
        return $this;
    }

    /**
     * Get info
     *
     * @return string 
     */
    public function getInfo()
    {
        return $this->info;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add translations
     *
     * @param \AdEntify\CoreBundle\Entity\InformationTranslation $translations
     * @return Information
     */
    public function addTranslation(\AdEntify\CoreBundle\Entity\InformationTranslation $translations)
    {
        if (!$this->translations->contains($translations)) {
            $this->translations[] = $translations;
            $translations->setObject($this);
        }

        return $this;
    }

    /**
     * Remove translations
     *
     * @param \AdEntify\CoreBundle\Entity\InformationTranslation $translations
     */
    public function removeTranslation(\AdEntify\CoreBundle\Entity\InformationTranslation $translations)
    {
        $this->translations->removeElement($translations);
    }

    /**
     * Get translations
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTranslations()
    {
        return $this->translations;
    }
}