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
     * @Serializer\Groups({"me", "details", "list", "slight-list"})
     */
    private $id;

    /**
     * @Serializer\Groups({"me", "details", "list", "slight-list"})
     *
     * @var integer
     *
     * @ORM\Column(name="provider_id", type="bigint", nullable=true)
     */
    private $providerId;

    /**
     * @Serializer\Groups({"details", "list"})
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\ProductProvider",cascade={"persist"})
     */
    private $productProvider;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     * @Assert\NotBlank(message="brand.name.notblank")
     * @Serializer\Groups({"me", "details", "list", "slight-list"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     * @Serializer\Groups({"me", "details", "list", "slight-list"})
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="website_url", type="string", length=255, nullable=true)
     * @Serializer\Groups({"me", "details"})
     */
    private $websiteUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook_url", type="string", length=255, nullable=true)
     * @Serializer\Groups({"me", "details"})
     */
    private $facebookUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="twitter_url", type="string", length=255, nullable=true)
     * @Serializer\Groups({"me", "details"})
     */
    private $twitterUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="pinterest_url", type="string", length=255, nullable=true)
     * @Serializer\Groups({"details"})
     */
    private $pinterestUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="instagram_url", type="string", length=255, nullable=true)
     * @Serializer\Groups({"me", "details"})
     */
    private $instagramUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="tumblr_url", type="string", length=255, nullable=true)
     * @Serializer\Groups({"me", "details"})
     */
    private $tumblrUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="original_logo_url", type="string", length=255, nullable=true)
     * @Gedmo\UploadableFilePath
     */
    private $originalLogoUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="large_logo_url", type="text", nullable=true)
     * @Serializer\Groups({"me", "details", "list", "slight-list"})
     */
    private $largeLogoUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="medium_logo_url", type="text", nullable=true)
     * @Serializer\Groups({"me", "details", "list", "slight-list"})
     */
    private $mediumLogoUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="small_logo_url", type="text", nullable=true)
     * @Serializer\Groups({"me", "details", "list", "slight-list"})
     */
    private $smallLogoUrl;

    /**
     * @var datetime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="added_at", type="datetime")
     * @Assert\DateTime()
     * @Serializer\Groups({"me", "details", "list", "slight-list"})
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
     * @Serializer\Groups({"me", "details", "list"})
     */
    private $productsCount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="tags_count", type="integer")
     * @Serializer\Groups({"me", "details", "list"})
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
     * @Serializer\Groups({"me", "details", "list", "slight-list"})
     */
    private $slug;

    /**
     * @Serializer\Exclude
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\Venue", mappedBy="brands")
     */
    private $venues;

    /**
     * @var integer
     *
     * @ORM\Column(name="venues_count", type="integer")
     * @Serializer\Groups({"me", "details", "list"})
     */
    private $venuesCount = 0;

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
     * @Serializer\Groups({"me", "details", "list"})
     */
    private $followersCount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="min_age", type="integer")
     * @Serializer\Groups({"me", "details", "list"})
     */
    private $minAge = 13;

    /**
     * @var string
     *
     * @ORM\Column(name="legal_notice", type="text", nullable=true)
     * @Serializer\Groups({"me", "details", "list"})
     */
    private $legalNotice;

    /**
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\User", mappedBy="brands", cascade={"persist"})
     * @Serializer\Exclude
     */
    private $admins;

    /**
     * @var boolean
     *
     * @ORM\Column(name="validated", type="boolean")
     * @Serializer\Groups({"me", "details", "list"})
     */
    private $validated = false;

    /**
     * @Serializer\Exclude
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Notification", mappedBy="brand")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $notifications;

    /**
     * @Serializer\Exclude
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Action", mappedBy="target")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $actions;

    /**
     * @Serializer\Exclude
     *
     * @var integer
     *
     * * @ORM\Column(name="tag_required_addict_reward", type="integer")
     */
    private $tagsRequiredAddictReward = 20;

    /**
     * @Serializer\Exclude
     *
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Reward", mappedBy="brand")
     */
    private $rewards;

    /**
     * @Serializer\Exclude
     *
     * @var $goldFansPercentage
     *
     * @ORM\Column(name="gold_fans_percentage", type="decimal", scale=4, precision=15)
     */
    private $goldFansPercentage = 1;

    /**
     * @Serializer\Exclude
     *
     * @var $silverFansPercentage
     *
     * @ORM\Column(name="silver_fans_percentage", type="decimal", scale=4, precision=15)
     */
    private $silverFansPercentage = 2;

    /**
     * @Serializer\Exclude
     *
     * @var $bronzeFansPercentage
     *
     * @ORM\Column(name="bronze_fans_percentage", type="decimal", scale=4, precision=15)
     */
    private $bronzeFansPercentage = 3;

    /**
     * @var int
     */
    private $photosCount = 0;

    /**
     * @var string
     */
    private $logoUrl;

    /**
     * @Serializer\Groups({"details"})
     */
    private $lastPhoto;

    /**
     * @Serializer\Groups({"details"})
     */
    private $randomPhoto;

    public function __construct()
    {
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
        $this->venues = new \Doctrine\Common\Collections\ArrayCollection();
        $this->itemTags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->followers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->notifications = new \Doctrine\Common\Collections\ArrayCollection();
        $this->actions = new \Doctrine\Common\Collections\ArrayCollection();
        $this->rewards = new \Doctrine\Common\Collections\ArrayCollection();
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
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

    /**
     * @param string $tumblrUrl
     */
    public function setTumblrUrl($tumblrUrl)
    {
        $this->tumblrUrl = $tumblrUrl;
    }

    /**
     * @return string
     */
    public function getTumblrUrl()
    {
        return $this->tumblrUrl;
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
     * @param boolean $validated
     */
    public function setValidated($validated)
    {
        $this->validated = $validated;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getValidated()
    {
        return $this->validated;
    }

    public function addNotification(Notification $notification)
    {
        $this->notifications[] = $notification;
        $notification->setBrand($this);
        return $this;
    }

    public function removeNotification(Notification $notification)
    {
        $this->notifications->removeElement($notification);
        $notification->setBrand(null);
    }

    public function getNotifications()
    {
        return $this->notifications;
    }

    public function addAction(Action $action)
    {
        $this->actions[] = $action;
        $action->setBrand($this);
        return $this;
    }

    public function removeAction(Action $action)
    {
        $this->actions->removeElement($action);
        $action->setBrand(null);
    }

    /**
     * @return mixed
     */
    public function getActions()
    {
        return $this->actions;
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

    /**
     * @param int $tagsRequiredAddictReward
     */
    public function setTagsRequiredAddictReward($tagsRequiredAddictReward)
    {
        $this->tagsRequiredAddictReward = $tagsRequiredAddictReward;
        return $this;
    }

    /**
     * @return int
     */
    public function getTagsRequiredAddictReward()
    {
        return $this->tagsRequiredAddictReward;
    }

    public function addReward(Reward $reward)
    {
        $this->rewards[] = $reward;
        $reward->setBrand($this);
        return $this;
    }

    public function removeReward(Reward $reward)
    {
        $this->rewards->removeElement($reward);
        $reward->setBrand(null);
    }

    public function getRewards()
    {
        return $this->rewards;
    }

    /**
     * @param mixed $bronzeFansPercentage
     */
    public function setBronzeFansPercentage($bronzeFansPercentage)
    {
        $this->bronzeFansPercentage = $bronzeFansPercentage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBronzeFansPercentage()
    {
        return $this->bronzeFansPercentage;
    }

    /**
     * @param mixed $goldFansPercentage
     */
    public function setGoldFansPercentage($goldFansPercentage)
    {
        $this->goldFansPercentage = $goldFansPercentage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGoldFansPercentage()
    {
        return $this->goldFansPercentage;
    }

    /**
     * @param mixed $silverFansPercentage
     */
    public function setSilverFansPercentage($silverFansPercentage)
    {
        $this->silverFansPercentage = $silverFansPercentage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSilverFansPercentage()
    {
        return $this->silverFansPercentage;
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

    /**
     * @param mixed $randomPhoto
     */
    public function setRandomPhoto($randomPhoto)
    {
        $this->randomPhoto = $randomPhoto;
    }

    /**
     * @return mixed
     */
    public function getRandomPhoto()
    {
        return $this->randomPhoto;
    }

    /**
     * @return int
     */
    public function getProviderId()
    {
        return $this->providerId;
    }

    /**
     * @param int $providerId
     */
    public function setProviderId($providerId)
    {
        $this->providerId = $providerId;
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

    /**
     * @param Admin $admin
     * @return $this
     */
    public function addAdmin(User $admin)
    {
        $this->admins[] = $admin;
        return $this;
    }

    /**
     * @param Admin $admin
     */
    public function removeAdmin(User $admin)
    {
        $this->admins->removeElement($admin);
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAdmins()
    {
        return $this->admins;
    }
}