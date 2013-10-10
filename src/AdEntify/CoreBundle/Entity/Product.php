<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 08/04/2013
 * Time: 14:58
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Product
 *
 * @Serializer\XmlRoot("product")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="products", indexes={@ORM\Index(name="search_idx", columns={"name"})})
 * @ORM\Entity
 */
class Product
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
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text")
     * @Assert\NotBlank
     */
    private $description;

    /**
     * @var datetime $created_on
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @Assert\DateTime()
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\Brand", inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     */
    private $brand;

    /**
     * @Serializer\Exclude
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Tag", mappedBy="product")
     * @ORM\OrderBy({"createdAt" = "ASC"})
     */
    private $tags;

    /**
     * @var integer
     *
     * @ORM\Column(name="tags_count", type="integer")
     */
    private $tagsCount = 0;

    /**
     * @Serializer\Exclude
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\Venue", mappedBy="products")
     */
    private $purchaseVenues;

    /**
     * @var string
     *
     * @ORM\Column(name="purchase_url", type="text", nullable=true)
     */
    private $purchaseUrl;

    /**
     * @Serializer\Exclude
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\ShortUrl")
     */
    private $purchaseShortUrl;

    /**
     * @Serializer\Exclude
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\Person", mappedBy="products")
     */
    private $owners;

    /**
     * @var string
     *
     * @ORM\Column(name="medium_url", type="text", nullable=true)
     */
    private $mediumUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="small_url", type="text", nullable=true)
     */
    private $smallUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="original_url", type="text")
     */
    private $originalUrl;

    public function __construct()
    {
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->owners = new \Doctrine\Common\Collections\ArrayCollection();
        $this->purchaseVenues = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $brand
     * @return Product
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
        return $this;
    }

    /**
     * @return Brand
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @return int
     */
    public function getTagsCount()
    {
        return $this->tagsCount;
    }

    public function addTag(\AdEntify\CoreBundle\Entity\Tag $tag)
    {
        $this->tags[] = $tag;
        $tag->setProduct($this);
        return $this;
    }

    public function removeTag(\AdEntify\CoreBundle\Entity\Tag $tag)
    {
        $this->tags->removeElement($tag);
        $tag->setProduct(null);
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function addPurchaseVenue(\AdEntify\CoreBundle\Entity\Venue $venue)
    {
        $this->purchaseVenues[] = $venue;
        return $this;
    }

    public function removePurchaseVenue(\AdEntify\CoreBundle\Entity\Venue $venue)
    {
        $this->purchaseVenues->removeElement($venue);
    }

    public function getPurchaseVenues()
    {
        return $this->purchaseVenues;
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

    public function addOwner(Person $person)
    {
        $this->owners[] = $person;
        return $this;
    }

    public function removeOwner(Person $person)
    {
        $this->owners->removeElement($person);
    }

    public function getOwners()
    {
        return $this->owners;
    }

    /**
     * @param string $mediumUrl
     */
    public function setMediumUrl($mediumUrl)
    {
        $this->mediumUrl = $mediumUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getMediumUrl()
    {
        return $this->mediumUrl;
    }

    /**
     * @param string $originalUrl
     */
    public function setOriginalUrl($originalUrl)
    {
        $this->originalUrl = $originalUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalUrl()
    {
        return $this->originalUrl;
    }

    /**
     * @param string $smallUrl
     */
    public function setSmallUrl($smallUrl)
    {
        $this->smallUrl = $smallUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getSmallUrl()
    {
        return $this->smallUrl;
    }

    /**
     * @param string $purchaseUrl
     */
    public function setPurchaseUrl($purchaseUrl)
    {
        $this->purchaseUrl = $purchaseUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getPurchaseUrl()
    {
        return $this->purchaseUrl;
    }

    /**
     * @param mixed $purchaseShortUrl
     */
    public function setPurchaseShortUrl($purchaseShortUrl)
    {
        $this->purchaseShortUrl = $purchaseShortUrl;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPurchaseShortUrl()
    {
        return $this->purchaseShortUrl;
    }
}