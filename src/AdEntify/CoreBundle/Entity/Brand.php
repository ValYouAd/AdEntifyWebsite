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
 * @ORM\Entity(repositoryClass="AdEntify\CoreBundle\Entity\BrandRepository")
 * @Gedmo\Uploadable(path="uploads/brands/logo-original", filenameGenerator="SHA1", allowOverwrite=true, appendNumber=true, allowedTypes="image/png,image/jpg,image/jpeg,image/gif", maxSize=2097152)
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
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="website_url", type="string", length=255, nullable=true)
     */
    private $websiteUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook_url", type="string", length=255, nullable=true)
     */
    private $facebookUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="twitter_url", type="string", length=255, nullable=true)
     */
    private $twitterUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="pinterest_url", type="string", length=255, nullable=true)
     */
    private $pinterestUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="instagram_url", type="string", length=255, nullable=true)
     */
    private $instagramUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="original_logo_url", type="string", length=255)
     * @Gedmo\UploadableFilePath
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
    private $itemTags;

    /**
     * @var integer
     *
     * @ORM\Column(name="tags_count", type="integer")
     */
    private $tagsCount = 0;

    /**
     * @Serializer\Exclude
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Tag", mappedBy="brand")
     * @ORM\OrderBy({"createdAt" = "ASC"})
     */
    private $tags;

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
    private $costPerTag = 0;

    /**
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\Category", inversedBy="brands")
     */
    private $categories;

    /**
     * @Serializer\Exclude
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\User", inversedBy="followedBrands")
     */
    private $followers;

    /**
     * @var integer
     *
     * @ORM\Column(name="followers_count", type="integer")
     */
    private $followersCount = 0;

    private $photosCount = 0;

    private $logoUrl;

    public function __construct()
    {
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
        $this->venues = new \Doctrine\Common\Collections\ArrayCollection();
        $this->itemTags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->followers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param mixed $logoUrl
     */
    public function setLogoUrl($logoUrl)
    {
        $this->logoUrl = $logoUrl;
    }

    /**
     * @return mixed
     */
    public function getLogoUrl()
    {
        return $this->logoUrl;
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
     * @param int $tagsCount
     */
    public function setTagsCount($tagsCount)
    {
        $this->tagsCount = $tagsCount;
        return $this;
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

    public function addItemTag(\AdEntify\CoreBundle\Entity\BrandTag $tag)
    {
        $this->itemTags[] = $tag;
        $tag->setBrand($this);
        return $this;
    }

    public function removeItemTag(\AdEntify\CoreBundle\Entity\BrandTag $tag)
    {
        $this->itemTags->removeElement($tag);
        $tag->setBrand(null);
    }

    public function getItemTags()
    {
        return $this->itemTags;
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
     * @param string $facebookUrl
     */
    public function setFacebookUrl($facebookUrl)
    {
        $this->facebookUrl = $facebookUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getFacebookUrl()
    {
        return $this->facebookUrl;
    }

    /**
     * @param string $instagramUrl
     */
    public function setInstagramUrl($instagramUrl)
    {
        $this->instagramUrl = $instagramUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getInstagramUrl()
    {
        return $this->instagramUrl;
    }

    /**
     * @param string $pinterestUrl
     */
    public function setPinterestUrl($pinterestUrl)
    {
        $this->pinterestUrl = $pinterestUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getPinterestUrl()
    {
        return $this->pinterestUrl;
    }

    /**
     * @param string $twitterUrl
     */
    public function setTwitterUrl($twitterUrl)
    {
        $this->twitterUrl = $twitterUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getTwitterUrl()
    {
        return $this->twitterUrl;
    }

    /**
     * @param string $websiteUrl
     */
    public function setWebsiteUrl($websiteUrl)
    {
        $this->websiteUrl = $websiteUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getWebsiteUrl()
    {
        return $this->websiteUrl;
    }

    public function addTag(Tag $tag)
    {
        $this->tags[] = $tag;
        $tag->setBrand($this);
        return $this;
    }

    public function removeTag(Tag $tag)
    {
        $this->tags->removeElement($tag);
        $tag->setBrand(null);
    }

    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param Category $category
     * @return $this
     */
    public function addCategory(Category $category)
    {
        $category->addBrand($this);
        $this->categories[] = $category;
        return $this;
    }

    /**
     * @param Category $category
     */
    public function removeCategory(Category $category)
    {
        $category->removeBrand($this);
        $this->categories->removeElement($category);
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function addFollower(User $user)
    {
        $user->addFollowedBrand($this);
        $this->followers[] = $user;
        return $this;
    }

    /**
     * @param User $user
     */
    public function removeFollower(User $user)
    {
        $user->removeFollowedBrand($this);
        $this->followers->removeElement($user);
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFollowers()
    {
        return $this->followers;
    }

    /**
     * @param int $followersCount
     */
    public function setFollowersCount($followersCount)
    {
        $this->followersCount = $followersCount;
    }

    /**
     * @return int
     */
    public function getFollowersCount()
    {
        return $this->followersCount;
    }
}