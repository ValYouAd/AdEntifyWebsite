<?php

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Hashtag
 *
 * @ORM\Table(name="hashtags", indexes={@ORM\Index(name="search_idx", columns={"name"})})
 * @ORM\Entity(repositoryClass="AdEntify\CoreBundle\Entity\HashtagRepository")
 */
class Hashtag
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="used_count", type="integer")
     */
    private $usedCount = 0;

    /**
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\Photo", mappedBy="hashtags", cascade={"persist"})
     *
     * @Serializer\Exclude
     */
    private $photos;

    public function __construct()
    {
        $this->photos = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set name
     *
     * @param string $name
     * @return Hashtag
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    public function addPhoto(Photo $photo)
    {
        $this->photos[] = $photo;
        return $this;
    }

    public function removePhoto(Photo $photo)
    {
        $this->photos->removeElement($photo);
    }

    public function getPhotos()
    {
        return $this->photos;
    }

    /**
     * @param int $usedCount
     */
    public function setUsedCount($usedCount)
    {
        $this->usedCount = $usedCount;
    }

    /**
     * @return int
     */
    public function getUsedCount()
    {
        return $this->usedCount;
    }
}
