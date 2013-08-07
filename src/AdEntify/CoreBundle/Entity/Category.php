<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 11/06/2013
 * Time: 17:51
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Category
 *
 * @Serializer\XmlRoot("category")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="categories", indexes={@ORM\Index(name="search_idx", columns={"name","visible"})})
 * @ORM\Entity
 * @Gedmo\TranslationEntity(class="AdEntify\CoreBundle\Entity\CategoryTranslation")
 */
class Category
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
     * @Gedmo\Translatable
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var boolean
     *
     * @ORM\Column(name="visible", type="boolean")
     */
    private $visible = true;

    /**
     * @var datetime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

    /**
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\Photo", mappedBy="categories", cascade={"persist"})
     *
     * @Serializer\Exclude
     */
    private $photos;

    public function __construct()
    {
        $this->photos = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param \AdEntify\CoreBundle\Entity\datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \AdEntify\CoreBundle\Entity\datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param boolean $visible
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getVisible()
    {
        return $this->visible;
    }

    /**
     * @param mixed $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
}