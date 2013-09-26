<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 16/04/2013
 * Time: 12:11
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Photo
 *
 * @Serializer\XmlRoot("photo")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="photos", indexes={@ORM\Index(name="search_idx", columns={"source", "status"})})
 * @ORM\Entity
 */
class Photo
{
    const STATUS_PROCESSING =  "processing";
    const STATUS_UNPROCESSED = "unprocessed";
    const STATUS_LOAD_ERROR = "load-error";
    const STATUS_READY =       "ready";

    const SOURCE_FACEBOOK = "facebook";
    const SOURCE_FLICKR = "flickr";
    const SOURCE_INSTAGRAM = "instagram";
    const SOURCE_GOOGLEPLUS = "googleplus";
    const SOURCE_LOCAL = "local";
    const SOURCE_WORDPRESS = "wordpress";

    const SCOPE_PUBLIC = 'public';
    const SCOPE_PRIVATE = 'private';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var datetime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var datetime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=50)
     */
    private $status = self::STATUS_UNPROCESSED;

    /**
     * @var string
     *
     * @ORM\Column(name="source", type="string", length=50, nullable=true)
     */
    private $source;

    /**
     * @var string
     *
     * @ORM\Column(name="photo_source_id", type="string", length=100, nullable=true)
     */
    private $photoSourceId;

    /**
     * @var string
     *
     * @ORM\Column(name="caption", type="string", length=255, nullable=true)
     */
    private $caption;

    /**
     * @var string
     *
     * @ORM\Column(name="original_url", type="text")
     * @Assert\NotBlank()
     */
    private $originalUrl;

    /**
     * @var integer
     *
     * @ORM\Column(name="original_width", type="smallint", nullable=true)
     */
    private $originalWidth;

    /**
     * @var integer
     *
     * @ORM\Column(name="original_height", type="smallint", nullable=true)
     */
    private $originalHeight;

    /**
     * @var string
     *
     * @ORM\Column(name="large_url", type="text", nullable=true)
     */
    private $largeUrl;

    /**
     * @var integer
     *
     * @ORM\Column(name="large_width", type="smallint", nullable=true)
     */
    private $largeWidth;

    /**
     * @var integer
     *
     * @ORM\Column(name="large_height", type="smallint", nullable=true)
     */
    private $largeHeight;

    /**
     * @var string
     *
     * @ORM\Column(name="medium_url", type="text", nullable=true)
     */
    private $mediumUrl;

    /**
     * @var integer
     *
     * @ORM\Column(name="medium_width", type="smallint", nullable=true)
     */
    private $mediumWidth;

    /**
     * @var integer
     *
     * @ORM\Column(name="medium_height", type="smallint", nullable=true)
     */
    private $mediumHeight;

    /**
     * @var string
     *
     * @ORM\Column(name="small_url", type="text", nullable=true)
     */
    private $smallUrl;

    /**
     * @var integer
     *
     * @ORM\Column(name="small_width", type="smallint", nullable=true)
     */
    private $smallWidth;

    /**
     * @var integer
     *
     * @ORM\Column(name="small_height", type="smallint", nullable=true)
     */
    private $smallHeight;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\User", inversedBy="photos")
     */
    private $owner;

    /**
     * @Serializer\Exclude
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Comment", mappedBy="photo", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt" = "ASC"})
     */
    private $comments;

    /**
     * @var integer
     *
     * @ORM\Column(name="comments_count", type="integer")
     */
    private $commentsCount = 0;

    /**
     * @Serializer\Exclude
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Like", mappedBy="photo", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt" = "ASC"})
     */
    private $likes;

    /**
     * @var integer
     *
     * @ORM\Column(name="likes_count", type="integer")
     */
    private $likesCount = 0;

    /**
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Tag", mappedBy="photo")
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
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\Category", inversedBy="photos")
     *
     * @Serializer\Exclude
     */
    private $categories;

    /**
     * @var string
     *
     * @ORM\Column(name="visibility_scope", length=100, type="string")
     */
    private $visibilityScope = self::SCOPE_PUBLIC;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\Venue", inversedBy="photos")
     * @ORM\JoinColumn(nullable=true)
     */
    private $venue;

    /**
     * @var float
     *
     * @ORM\Column(name="latitude", type="float", length=10, scale=6, nullable=true)
     */
    private $latitude;

    /**
     * @var float
     *
     * @ORM\Column(name="longitude", type="float", length=10, scale=6, nullable=true)
     */
    private $longitude;

    /**
     * @Serializer\Exclude
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\User", mappedBy="favoritesPhotos")
     */
    private $favoritesUsers;

    public function __construct()
    {
        $this->likes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->favoritesUsers = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param \AdEntify\CoreBundle\Entity\datetime $createdAt
     * @return Photo
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
     * @param string $caption
     * @return Photo
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;
        return $this;
    }

    /**
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * @param string $status
     * @return Photo
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param int $largeHeight
     */
    public function setLargeHeight($largeHeight)
    {
        $this->largeHeight = $largeHeight;
        return $this;
    }

    /**
     * @return int
     */
    public function getLargeHeight()
    {
        return $this->largeHeight;
    }

    /**
     * @param string $largeUrl
     */
    public function setLargeUrl($largeUrl)
    {
        $this->largeUrl = $largeUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getLargeUrl()
    {
        return $this->largeUrl;
    }

    /**
     * @param int $largeWidth
     */
    public function setLargeWidth($largeWidth)
    {
        $this->largeWidth = $largeWidth;
        return $this;
    }

    /**
     * @return int
     */
    public function getLargeWidth()
    {
        return $this->largeWidth;
    }

    /**
     * @param int $mediumHeight
     */
    public function setMediumHeight($mediumHeight)
    {
        $this->mediumHeight = $mediumHeight;
        return $this;
    }

    /**
     * @return int
     */
    public function getMediumHeight()
    {
        return $this->mediumHeight;
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
     * @param int $mediumWidth
     */
    public function setMediumWidth($mediumWidth)
    {
        $this->mediumWidth = $mediumWidth;
        return $this;
    }

    /**
     * @return int
     */
    public function getMediumWidth()
    {
        return $this->mediumWidth;
    }

    /**
     * @param int $originalHeight
     */
    public function setOriginalHeight($originalHeight)
    {
        $this->originalHeight = $originalHeight;
        return $this;
    }

    /**
     * @return int
     */
    public function getOriginalHeight()
    {
        return $this->originalHeight;
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
     * @param int $originalWidth
     */
    public function setOriginalWidth($originalWidth)
    {
        $this->originalWidth = $originalWidth;
        return $this;
    }

    /**
     * @return int
     */
    public function getOriginalWidth()
    {
        return $this->originalWidth;
    }

    /**
     * @param int $smallHeight
     */
    public function setSmallHeight($smallHeight)
    {
        $this->smallHeight = $smallHeight;
        return $this;
    }

    /**
     * @return int
     */
    public function getSmallHeight()
    {
        return $this->smallHeight;
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
     * @param int $smallWidth
     */
    public function setSmallWidth($smallWidth)
    {
        $this->smallWidth = $smallWidth;
        return $this;
    }

    /**
     * @return int
     */
    public function getSmallWidth()
    {
        return $this->smallWidth;
    }

    public function addComment(\AdEntify\CoreBundle\Entity\Comment $comment)
    {
        $this->comments[] = $comment;
        $comment->setPhoto($this);
        return $this;
    }

    public function removeComment(\AdEntify\CoreBundle\Entity\Comment $comment)
    {
        $this->comments->removeElement($comment);
        $comment->setPhoto(null);
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function addLike(\AdEntify\CoreBundle\Entity\Like $like)
    {
        $this->likes[] = $like;
        $like->setPhoto($this);
        return $this;
    }

    public function removeLike(\AdEntify\CoreBundle\Entity\Like $like)
    {
        $this->likes->removeElement($like);
        $like->setPhoto(null);
    }

    public function getLikes()
    {
        return $this->likes;
    }

    /**
     * @return int
     */
    public function getCommentsCount()
    {
        return $this->commentsCount;
    }

    /**
     * @return int
     */
    public function getLikesCount()
    {
        return $this->likesCount;
    }

    /**
     * @param $owner
     * @return Photo
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
        return $this;
    }

    public function getOwner()
    {
        return $this->owner;
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
        $tag->setPhoto($this);
        return $this;
    }

    public function removeTag(\AdEntify\CoreBundle\Entity\Tag $tag)
    {
        $this->tags->removeElement($tag);
        $tag->setPhoto(null);
    }

    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = $source;
        return $this;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    public function addCategory(\AdEntify\CoreBundle\Entity\Category $category)
    {
        $category->addPhoto($this);
        $this->categories[] = $category;
        return $this;
    }

    public function removeCategory(\AdEntify\CoreBundle\Entity\Category $category)
    {
        $category->removePhoto($this);
        $this->categories->removeElement($category);
    }

    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param int $visibilityScope
     */
    public function setVisibilityScope($visibilityScope)
    {
        $this->visibilityScope = $visibilityScope;
        return $this;
    }

    /**
     * @return int
     */
    public function getVisibilityScope()
    {
        return $this->visibilityScope;
    }

    public function setVenue($venue)
    {
        $this->venue = $venue;
        return $venue;
    }

    public function getVenue()
    {
        return $this->venue;
    }

    /**
     * @param float $latitude
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param float $longitude
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param string $photoSourceId
     */
    public function setPhotoSourceId($photoSourceId)
    {
        $this->photoSourceId = $photoSourceId;
        return $this;
    }

    /**
     * @return string
     */
    public function getPhotoSourceId()
    {
        return $this->photoSourceId;
    }

    public function addFavoriteUser(\AdEntify\CoreBundle\Entity\User $user)
    {
        $this->favoritesUsers[] = $user;
        return $this;
    }

    public function removeFavoriteUser(\AdEntify\CoreBundle\Entity\User $user)
    {
        $this->favoritesUsers->removeElement($user);
    }

    public function getFavoritesUsers()
    {
        return $this->favoritesUsers;
    }

    /**
     * @param \AdEntify\CoreBundle\Entity\datetime $deletedAt
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    /**
     * @return \AdEntify\CoreBundle\Entity\datetime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }
}