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
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Serializer\Groups({"details", "list"})
     */
    private $description;

    /**
     * @var datetime $created_on
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @Assert\DateTime()
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\Brand", inversedBy="products")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Groups({"details", "list"})
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
     * @Serializer\Groups({"details", "list"})
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
     * @Serializer\Groups({"details", "list"})
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
     * @Serializer\Groups({"details", "list"})
     */
    private $mediumUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="small_url", type="text", nullable=true)
     * @Serializer\Groups({"details", "list"})
     */
    private $smallUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="original_url", type="text", nullable=true)
     * @Serializer\Groups({"details", "list"})
     */
    private $originalUrl;

    /**
     * @var integer
     *
     * * @ORM\Column(name="min_age", type="integer")
     * @Serializer\Groups({"details", "list"})
     */
    private $minAge = 13;

    /**
     * @var string
     *
     * @ORM\Column(name="legal_notice", type="text", nullable=true)
     * @Serializer\Groups({"details", "list"})
     */
    private $legalNotice;

    /**
     * @Serializer\Exclude
     *
     * @var
     *
     * @ORM\Column(name="cost_per_tag", type="decimal", scale=4, precision=15)
     */
    private $costPerTag = 0;

    /**
     * @Serializer\Exclude
     *
     * @var
     *
     * @ORM\Column(name="adentify_fees", type="decimal", scale=4, precision=15)
     */
    private $adentifyFees = 50;

    /**
     * @Serializer\Exclude
     *
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Reward", mappedBy="product")
     */
    private $rewards;

    /**
     * @Serializer\Groups({"details"})
     */
    private $lastPhoto;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\ProductProvider")
     */
    private $productProvider;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\ProductRetailer")
     */
    private $productRetailer;

    /**
     * @return mixed
     */
    public function getProductRetailer()
    {
        return $this->productRetailer;
    }

    /**
     * @param mixed $productRetailer
     */
    public function setProductRetailer($productRetailer)
    {
        $this->productRetailer = $productRetailer;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductProvider()
    {
        return $this->productProvider;
    }

    /**
     * @param mixed $productProvider
     */
    public function setProductProvider($productProvider)
    {
        $this->productProvider = $productProvider;

        return $this;
    }

    public function __construct()
    {
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->owners = new \Doctrine\Common\Collections\ArrayCollection();
        $this->purchaseVenues = new \Doctrine\Common\Collections\ArrayCollection();
        $this->rewards = new \Doctrine\Common\Collections\ArrayCollection();
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
        return $this;hostDomain
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

    /**
     * @param string $legalNotice
     */
    public function setLegalNotice($legalNotice)
    {
        $this->legalNotice = $legalNotice;
        return $this;
    }

    /**
     * @return string
     */
    public function getLegalNotice()
    {
        return $this->legalNotice;
    }

    /**
     * @param int $minAge
     */
    public function setMinAge($minAge)
    {
        $this->minAge = $minAge;
        return $this;
    }

    /**
     * @return int
     */
    public function getMinAge()
    {
        return $this->minAge;
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

    /**
     * @param mixed $adentifyFees
     */
    public function setAdentifyFees($adentifyFees)
    {
        $this->adentifyFees = $adentifyFees;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdentifyFees()
    {
        return $this->adentifyFees;
    }

    public function addReward(Reward $reward)
    {
        $this->rewards[] = $reward;
        $reward->setProduct($this);
        return $this;
    }

    public function removeReward(Reward $reward)
    {
        $this->rewards->removeElement($reward);
        $reward->setProduct(null);
    }

    public function getRewards()
    {
        return $this->rewards;
    }

    /**
     * @param mixed $lastPhoto
     */
    public function setLastPhoto($lastPhoto)
    {
        $this->lastPhoto = $lastPhoto;
    }

    /**
     * @return mixed
     */
    public function getLastPhoto()
    {
        return $this->lastPhoto;
    }
}