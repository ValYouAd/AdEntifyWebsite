<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 16/04/2013
 * Time: 12:02
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Brand
 *
 * @Serializer\XmlRoot("brands")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="brands", indexes={@ORM\Index(name="search_idx", columns={"name"})})
 * @ORM\Entity
 */
class Brand
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
     * @Assert\NotBlank(message="brand.name.notblank")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="original_logo_url", type="text")
     * @Assert\NotBlank(message="brand.originalLogoUrl.notblank")
     */
    private $originalLogoUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="large_logo_url", type="text", nullable=true)
     */
    private $largeLogoUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="medium_logo_url", type="text", nullable=true)
     */
    private $mediumLogoUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="small_logo_url", type="text", nullable=true)
     */
    private $smallLogoUrl;

    /**
     * @var datetime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="added_at", type="datetime")
     * @Assert\DateTime()
     */
    private $added_at;

    /**
     * @Serializer\Exclude
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Product", mappedBy="brand")
     * @ORM\OrderBy({"createdAt" = "ASC"})
     */
    private $products;

    /**
     * @var integer
     *
     * @ORM\Column(name="products_count", type="integer")
     */
    private $productsCount = 0;

    /**
     * @Serializer\Exclude
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\BrandTag", mappedBy="brand")
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
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

    /**
     * @Serializer\Exclude
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\Venue", mappedBy="brand")
     */
    private $venues;

    /**
     * @var integer
     *
     * @ORM\Column(name="venues_count", type="integer")
     */
    private $venuesCount = 0;

    /**
     * @var
     *
     * @ORM\Column(name="cost_per_tag", type="decimal", scale=4, precision=15)
     */
    private $costPerTag;

    public function __construct()
    {
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
        $this->venues = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param string $name
     * @return Brand
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
     * @return int
     */
    public function getTagsCount()
    {
        return $this->tagsCount;
    }

    /**
     * @return int
     */
    public function getProductsCount()
    {
        return $this->productsCount;
    }

    public function addProduct(\AdEntify\CoreBundle\Entity\Product $product)
    {
        $this->products[] = $product;
        $product->setBrand($this);
        return $this;
    }

    public function removeProduct(\AdEntify\CoreBundle\Entity\Product $product)
    {
        $this->products->removeElement($product);
        $product->setBrand(null);
    }

    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param \AdEntify\CoreBundle\Entity\datetime $added_at
     */
    public function setAddedAt($added_at)
    {
        $this->added_at = $added_at;
        return $this;
    }

    /**
     * @return \AdEntify\CoreBundle\Entity\datetime
     */
    public function getAddedAt()
    {
        return $this->added_at;
    }

    /**
     * @param string $largeLogoUrl
     */
    public function setLargeLogoUrl($largeLogoUrl)
    {
        $this->largeLogoUrl = $largeLogoUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getLargeLogoUrl()
    {
        return $this->largeLogoUrl;
    }

    /**
     * @param string $mediumLogoUrl
     */
    public function setMediumLogoUrl($mediumLogoUrl)
    {
        $this->mediumLogoUrl = $mediumLogoUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getMediumLogoUrl()
    {
        return $this->mediumLogoUrl;
    }

    /**
     * @param string $originalLogoUrl
     */
    public function setOriginalLogoUrl($originalLogoUrl)
    {
        $this->originalLogoUrl = $originalLogoUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getOriginalLogoUrl()
    {
        return $this->originalLogoUrl;
    }

    /**
     * @param string $smallLogoUrl
     */
    public function setSmallLogoUrl($smallLogoUrl)
    {
        $this->smallLogoUrl = $smallLogoUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getSmallLogoUrl()
    {
        return $this->smallLogoUrl;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param int $venuesCount
     */
    public function setVenuesCount($venuesCount)
    {
        $this->venuesCount = $venuesCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getVenuesCount()
    {
        return $this->venuesCount;
    }

    public function addVenue(\AdEntify\CoreBundle\Entity\Venue $venue)
    {
        $this->venues[] = $venue;
        return $this;
    }

    public function removeVenue(\AdEntify\CoreBundle\Entity\Venue $venue)
    {
        $this->venues->removeElement($venue);
    }

    public function getVenues()
    {
        return $this->venues;
    }

    /**
     * @param mixed $costPerTag
     */
    public function setCostPerTag($costPerTag)
    {
        $this->costPerTag = $costPerTag;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCostPerTag()
    {
        return $this->costPerTag;
    }

    public function addTag(\AdEntify\CoreBundle\Entity\BrandTag $tag)
    {
        $this->tags[] = $tag;
        $tag->setBrand($this);
        return $this;
    }

    public function removeTag(\AdEntify\CoreBundle\Entity\BrandTag $tag)
    {
        $this->tags->removeElement($tag);
        $tag->setBrand(null);
    }

    public function getTags()
    {
        return $this->tags;
    }
}