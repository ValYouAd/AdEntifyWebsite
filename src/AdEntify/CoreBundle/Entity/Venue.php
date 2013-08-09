<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 18/04/2013
 * Time: 10:40
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Venue
 *
 * @Serializer\XmlRoot("venue")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="venues", indexes={@ORM\Index(name="search_idx", columns={"foursquare_id", "name"})})
 * @ORM\Entity
 */
class Venue
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
     * @ORM\Column(name="foursquare_id", type="string", length=255, unique=true, nullable=true)
     */
    private $foursquareId;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook_id", type="bigint", unique=true, nullable=true)
     */
    private $facebookId;

    /**
     * @var string
     *
     * @ORM\Column(name="foursquare_short_link", type="text", nullable=true)
     */
    private $foursquareShortLink;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Assert\NotBlank(message="venue.name.notblank")
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="text", nullable=true)
     */
    private $link;

    /**
     * @Serializer\Exclude
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\ShortUrl")
     */
    private $shortUrl;

    /**
     * @var datetime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @Assert\DateTime()
     */
    private $createdAt;

    /**
     * @var float
     *
     * @ORM\Column(name="latitude", type="float", length=10, scale=6)
     */
    private $lat;

    /**
     * @var float
     *
     * @ORM\Column(name="longitude", type="float", length=10, scale=6)
     */
    private $lng;

    /**
     * @var string
     *
     * @ORM\Column(name="address", type="text", nullable=true)
     */
    private $address;

    /**
     * @var string
     *
     * @ORM\Column(name="postal_code", type="string", length=5, nullable=true)
     */
    private $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="text", nullable=true)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(name="state", type="text", nullable=true)
     */
    private $state;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="text", nullable=true)
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="cc", type="string", length=4, nullable=true)
     */
    private $cc;

    /**
     * @Serializer\Exclude
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Tag", mappedBy="venue")
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
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Photo", mappedBy="venue")
     * @ORM\OrderBy({"createdAt" = "ASC"})
     */
    private $photos;

    /**
     * @var integer
     *
     * @ORM\Column(name="photos_count", type="integer")
     */
    private $photosCount = 0;

    /**
     * @Serializer\Exclude
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\Product", inversedBy="purchaseVenues")
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
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\Brand", inversedBy="venues")
     *
     * @Serializer\Exclude
     */
    private $brands;

    /**
     * @var integer
     *
     * @ORM\Column(name="brands_count", type="integer")
     */
    private $brandsCount = 0;

    public function __construct()
    {
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
        $this->photos = new \Doctrine\Common\Collections\ArrayCollection();
        $this->brands = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $foursquareId
     */
    public function setFoursquareId($foursquareId)
    {
        $this->foursquareId = $foursquareId;
        return $this;
    }

    /**
     * @return string
     */
    public function getFoursquareId()
    {
        return $this->foursquareId;
    }

    /**
     * @param string $foursquareShortLink
     */
    public function setFoursquareShortLink($foursquareShortLink)
    {
        $this->foursquareShortLink = $foursquareShortLink;
        return $this;
    }

    /**
     * @return string
     */
    public function getFoursquareShortLink()
    {
        return $this->foursquareShortLink;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \AdEntify\CoreBundle\Entity\double $lat
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
        return $this;
    }

    /**
     * @return \AdEntify\CoreBundle\Entity\double
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
        return $this;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param \AdEntify\CoreBundle\Entity\double $lng
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
        return $this;
    }

    /**
     * @return \AdEntify\CoreBundle\Entity\double
     */
    public function getLng()
    {
        return $this->lng;
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
     * @return int
     */
    public function getTagsCount()
    {
        return $this->tagsCount;
    }

    public function addTag(\AdEntify\CoreBundle\Entity\Tag $tag)
    {
        $this->tags[] = $tag;
        $tag->setVenue($this);
        return $this;
    }

    public function removeTag(\AdEntify\CoreBundle\Entity\Tag $tag)
    {
        $this->tags->removeElement($tag);
        $tag->setVenue(null);
    }

    public function getTags()
    {
        return $this->tags;
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
     * @return int
     */
    public function getProductsCount()
    {
        return $this->productsCount;
    }

    public function addProduct(\AdEntify\CoreBundle\Entity\Product $product)
    {
        $this->products[] = $product;
        $product->addPurchaseVenue($this);
        return $this;
    }

    public function removeProduct(\AdEntify\CoreBundle\Entity\Product $product)
    {
        $this->products->removeElement($product);
        $product->removePurchaseVenue($this);
    }

    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $cc
     */
    public function setCc($cc)
    {
        $this->cc = $cc;
        return $this;
    }

    /**
     * @return string
     */
    public function getCc()
    {
        return $this->cc;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $country
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $postalCode
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * @param string $state
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    public function addPhoto(\AdEntify\CoreBundle\Entity\Photo $photo)
    {
        $this->photos[] = $photo;
        $photo->setVenue($this);
        return $this;
    }

    public function removePhoto(\AdEntify\CoreBundle\Entity\Photo $photo)
    {
        $this->photos->removeElement($photo);
        $photo->setVenue(null);
    }

    public function getPhotos()
    {
        return $this->photos;
    }

    /**
     * @param int $photosCount
     */
    public function setPhotosCount($photosCount)
    {
        $this->photosCount = $photosCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getPhotosCount()
    {
        return $this->photosCount;
    }

    /**
     * @param string $facebookId
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;
        return $this;
    }

    /**
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    public function addBrand(Brand $brand)
    {
        $this->brands[] = $brand;
        $brand->addVenue($this);
        return $this;
    }

    public function removeBrand(Brand $brand)
    {
        $this->brands->removeElement($brand);
        $brand->removeVenue($this);
    }

    public function getBrands()
    {
        return $this->brands;
    }

    /**
     * @param int $brandsCount
     */
    public function setBrandsCount($brandsCount)
    {
        $this->brandsCount = $brandsCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getBrandsCount()
    {
        return $this->brandsCount;
    }

    /**
     * @param mixed $shortUrl
     */
    public function setShortUrl($shortUrl)
    {
        $this->shortUrl = $shortUrl;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getShortUrl()
    {
        return $this->shortUrl;
    }
}