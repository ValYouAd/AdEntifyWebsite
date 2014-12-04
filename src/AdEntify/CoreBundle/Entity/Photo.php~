<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 16/04/2013
 * Time: 12:11
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity;

use AdEntify\CoreBundle\Util\FileTools;
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
 * @ORM\Entity(repositoryClass="AdEntify\CoreBundle\Entity\PhotoRepository")
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
     * @Serializer\Groups({"details", "list", "slight-list"})
     *
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Serializer\Groups({"details", "list", "slight-list"})
     *
     * @var datetime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @Serializer\Groups({"details", "list", "slight-list"})
     *
     * @var datetime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @Serializer\Exclude()
     *
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=50)
     */
    private $status = self::STATUS_UNPROCESSED;

    /**
     * @Serializer\Groups({"details", "list", "slight-list"})
     *
     * @var string
     *
     * @ORM\Column(name="source", type="string", length=50, nullable=true)
     */
    private $source;

    /**
     * @Serializer\Groups({"details"})
     *
     * @var string
     *
     * @ORM\Column(name="photo_source_id", type="string", length=100, nullable=true)
     */
    private $photoSourceId;

    /**
     * @Serializer\Groups({"details", "list", "slight-list"})
     *
     * @var string
     *
     * @ORM\Column(name="caption", type="string", length=255, nullable=true)
     */
    private $caption;

    /**
     * @Serializer\Groups({"details"})
     *
     * @var string
     *
     * @ORM\Column(name="original_url", type="text")
     */
    private $originalUrl;

    /**
     * @Serializer\Groups({"details"})
     *
     * @var integer
     *
     * @ORM\Column(name="original_width", type="smallint", nullable=true)
     */
    private $originalWidth;

    /**
     * @Serializer\Groups({"details"})
     *
     * @var integer
     *
     * @ORM\Column(name="original_height", type="smallint", nullable=true)
     */
    private $originalHeight;

    /**
     * @var string
     *
     * @ORM\Column(name="large_url", type="text", nullable=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $largeUrl;

    /**
     * @var integer
     *
     * @ORM\Column(name="large_width", type="smallint", nullable=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $largeWidth;

    /**
     * @var integer
     *
     * @ORM\Column(name="large_height", type="smallint", nullable=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $largeHeight;

    /**
     * @var string
     *
     * @ORM\Column(name="retina_url", type="text", nullable=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $retinaUrl;

    /**
     * @var integer
     *
     * @ORM\Column(name="retina_width", type="smallint", nullable=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $retinaWidth;

    /**
     * @var integer
     *
     * @ORM\Column(name="retina_height", type="smallint", nullable=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $retinaHeight;

    /**
     * @var string
     *
     * @ORM\Column(name="medium_url", type="text", nullable=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $mediumUrl;

    /**
     * @var integer
     *
     * @ORM\Column(name="medium_width", type="smallint", nullable=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $mediumWidth;

    /**
     * @var integer
     *
     * @ORM\Column(name="medium_height", type="smallint", nullable=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $mediumHeight;

    /**
     * @var string
     *
     * @ORM\Column(name="small_url", type="text", nullable=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $smallUrl;

    /**
     * @var integer
     *
     * @ORM\Column(name="small_width", type="smallint", nullable=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $smallWidth;

    /**
     * @var integer
     *
     * @ORM\Column(name="small_height", type="smallint", nullable=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $smallHeight;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\User", inversedBy="photos")
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $owner;

    /**
     * @Serializer\Groups({"details"})
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Comment", mappedBy="photo", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt" = "ASC"})
     */
    private $comments;

    /**
     * @var integer
     *
     * @ORM\Column(name="comments_count", type="integer")
     * @Serializer\Groups({"details", "list"})
     */
    private $commentsCount = 0;

    /**
     * @Serializer\Groups({"details"})
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Like", mappedBy="photo", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt" = "ASC"})
     */
    private $likes;

    /**
     * @var integer
     *
     * @ORM\Column(name="likes_count", type="integer")
     * @Serializer\Groups({"details", "list"})
     */
    private $likesCount = 0;

    /**
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Tag", mappedBy="photo", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt" = "ASC"})
     * @Serializer\Groups({"details", "list"})
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
     *
     * @var
     *
     * @ORM\Column(name="total_tags_points", type="decimal", scale=4, precision=15)
     */
    private $totalTagsPoints = 0;

    /**
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\Category", inversedBy="photos")
     * @Serializer\Groups({"details", "list"})
     */
    private $categories;

    /**
     * @var string
     *
     * @ORM\Column(name="visibility_scope", length=100, type="string")
     * @Serializer\Groups({"details", "list"})
     */
    private $visibilityScope = self::SCOPE_PUBLIC;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\Venue", inversedBy="photos")
     * @ORM\JoinColumn(nullable=true)
     * @Serializer\Groups({"details"})
     */
    private $venue;

    /**
     * @var float
     *
     * @ORM\Column(name="latitude", type="float", length=10, scale=6, nullable=true)
     * @Serializer\Groups({"details"})
     */
    private $latitude;

    /**
     * @var float
     *
     * @ORM\Column(name="longitude", type="float", length=10, scale=6, nullable=true)
     * @Serializer\Groups({"details"})
     */
    private $longitude;

    /**
     * @Serializer\Exclude
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\User", mappedBy="favoritesPhotos")
     */
    private $favoritesUsers;

    /**
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\Hashtag", inversedBy="photos")
     * @Serializer\Groups({"details"})
     */
    private $hashtags;

    /**
     * @Serializer\Exclude
     *
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Report", mappedBy="photo", cascade={"remove"})
     */
    private $reports;

    /**
     * @Serializer\Exclude
     *
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Analytic", mappedBy="tag", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt" = "ASC"})
     */
    private $analytics;

    /**
     * @var integer
     *
     * @ORM\Column(name="views_count", type="integer")
     * @Serializer\Groups({"details", "list"})
     */
    private $viewsCount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="hovers_count", type="integer")
     * @Serializer\Groups({"details", "list"})
     */
    private $hoversCount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="tags_hovers_count", type="integer")
     * @Serializer\Groups({"details", "list"})
     */
    private $tagsHoversCount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="tags_clicks_count", type="integer")
     * @Serializer\Groups({"details", "list"})
     */
    private $tagsClicksCount = 0;

    /**
     * @var integer
     *
     * @ORM\Column(name="interaction_time", type="integer")
     * @Serializer\Groups({"details", "list"})
     */
    private $interactionTime = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="hovers_percentage", type="float")
     * @Serializer\Groups({"details", "list"})
     */
    private $hoversPercentage = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="tags_hovers_percentage", type="float")
     * @Serializer\Groups({"details", "list"})
     */
    private $tagsHoversPercentage = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="tags_clicks_percentage", type="float")
     * @Serializer\Groups({"details", "list"})
     */
    private $tagsClicksPercentage = 0;

    public function __construct()
    {
        $this->likes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->favoritesUsers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->hashtags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->reports = new \Doctrine\Common\Collections\ArrayCollection();
        $this->analytics = new \Doctrine\Common\Collections\ArrayCollection();
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

    public function clearComments()
    {
        $this->comments->clear();
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

    public function clearLikes()
    {
        $this->likes->clear();
    }

    public function getLikes()
    {
        return $this->likes;
    }

    /**
     * @param int $commentsCount
     */
    public function Count($commentsCount)
    {
        $this->commentsCount = $commentsCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getCommentsCount()
    {
        return $this->commentsCount;
    }

    /**
     * @param int $commentsCount
     */
    public function setCommentsCount($commentsCount)
    {
        $this->commentsCount = $commentsCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getLikesCount()
    {
        return $this->likesCount;
    }

    /**
     * @param int $likesCount
     */
    public function setLikesCount($likesCount)
    {
        $this->likesCount = $likesCount;
        return $this;
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
     * @param mixed $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
        return $this;
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
     * @param mixed $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
        return $this;
    }

    /**
     * @param string $visibilityScope
     */
    public function setVisibilityScope($visibilityScope)
    {
        $this->visibilityScope = $visibilityScope;
        return $this;
    }

    /**
     * @return string
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

    public function addHashtag($hashtag)
    {
        if (!is_object($hashtag))
            return $this;

        $hashtag->addPhoto($this);
        $this->hashtags[] = $hashtag;
        return $this;
    }

    public function removeHashtag($hashtag)
    {
        $hashtag->removePhoto($this);
        $this->hashtags->removeElement($hashtag);
    }

    public function getHashtags()
    {
        return $this->hashtags;
    }

    /**
     * @param mixed $hashtags
     */
    public function setHashtags($hashtags)
    {
        $this->hashtags = $hashtags;
        return $this;
    }

    /**
     * @param mixed $totalTagsPoints
     */
    public function setTotalTagsPoints($totalTagsPoints)
    {
        $this->totalTagsPoints = $totalTagsPoints;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTotalTagsPoints()
    {
        return $this->totalTagsPoints;
    }

    public function addReport(Report $report)
    {
        $this->reports[] = $report;
        $report->setPhoto($this);
        return $this;
    }

    public function removeReport(Report $report)
    {
        $this->reports->removeElement($report);
        $report->setPhoto(null);
    }

    public function getReports()
    {
        return $this->reports;
    }

    /**
     * @param int $retinaHeight
     */
    public function setRetinaHeight($retinaHeight)
    {
        $this->retinaHeight = $retinaHeight;
        return $this;
    }

    /**
     * @return int
     */
    public function getRetinaHeight()
    {
        return $this->retinaHeight;
    }

    /**
     * @param string $retinaUrl
     */
    public function setRetinaUrl($retinaUrl)
    {
        $this->retinaUrl = $retinaUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getRetinaUrl()
    {
        return $this->retinaUrl;
    }

    /**
     * @param int $retinaWidth
     */
    public function setRetinaWidth($retinaWidth)
    {
        $this->retinaWidth = $retinaWidth;
        return $this;
    }

    /**
     * @return int
     */
    public function getRetinaWidth()
    {
        return $this->retinaWidth;
    }

    public function fillThumbs($generatedThumbs)
    {
        foreach($generatedThumbs as $key => $value) {
            switch ($key) {
                case FileTools::PHOTO_SIZE_LARGE:
                    $this->setLargeUrl($value['filename']);
                    if (!empty($value['width']))
                        $this->setLargeWidth($value['width']);
                    if (!empty($value['height']))
                        $this->setLargeHeight($value['height']);
                    break;
                case FileTools::PHOTO_SIZE_RETINA:
                    $this->setRetinaUrl($value['filename']);
                    if (!empty($value['width']))
                        $this->setRetinaWidth($value['width']);
                    if (!empty($value['height']))
                        $this->setRetinaHeight($value['height']);
                    break;
                case FileTools::PHOTO_SIZE_MEDIUM:
                    $this->setMediumUrl($value['filename']);
                    if (!empty($value['width']))
                        $this->setMediumWidth($value['width']);
                    if (!empty($value['height']))
                        $this->setMediumHeight($value['height']);
                    break;
                case FileTools::PHOTO_SIZE_SMALLL:
                    $this->setSmallUrl($value['filename']);
                    if (!empty($value['width']))
                        $this->setSmallWidth($value['width']);
                    if (!empty($value['height']))
                        $this->setSmallHeight($value['height']);
                    break;
                case FileTools::PHOTO_SIZE_ORIGINAL:
                    $this->setOriginalUrl($value['filename']);
                    if (!empty($value['width']))
                        $this->setOriginalWidth($value['width']);
                    if (!empty($value['height']))
                        $this->setOriginalHeight($value['height']);
                    break;
            }
        }
    }

    public function addAnalytic(\AdEntify\CoreBundle\Entity\Analytic $analytic)
    {
        $this->analytics[] = $analytic;
        $analytic->setPhoto($this);
        return $this;
    }

    public function removeAnalytic(\AdEntify\CoreBundle\Entity\Analytic $analytic)
    {
        $this->analytics->removeElement($analytic);
        $analytic->setPhoto(null);
    }

    public function getAnalytics()
    {
        return $this->analytics;
    }

    /**
     * @return int
     */
    public function getHoversCount()
    {
	return $this->hoversCount;
    }

    /**
     * @param int $hoversCount
     */
    public function setHoversCount($hoversCount)
    {
	$this->hoversCount = $hoversCount;
	return $this;
    }

    /**
     * @return int
     */
    public function getTagsClicksCount()
    {
	return $this->tagsClicksCount;
    }

    /**
     * @param int $tagsClicksCount
     */
    public function setTagsClicksCount($tagsClicksCount)
    {
        $this->tagsClicksCount = $tagsClicksCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getTagsHoversCount()
    {
	    return $this->tagsHoversCount;
    }

    /**
     * @param int $tagsHoversCount
     */
    public function setTagsHoversCount($tagsHoversCount)
    {
        $this->tagsHoversCount = $tagsHoversCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getViewsCount()
    {
	    return $this->viewsCount;
    }

    /**
     * @param int $viewsCount
     */
    public function setViewsCount($viewsCount)
    {
        $this->viewsCount = $viewsCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getInteractionTime()
    {
        return $this->interactionTime;
    }

    /**
     * @param int $interactionTime
     */
    public function setInteractionTime($interactionTime)
    {
        $this->interactionTime = $interactionTime;
        return $this;
    }

    /**
     * Set hoversPercentage
     *
     * @param float $hoversPercentage
     * @return Photo
     */
    public function setHoversPercentage($hoversPercentage)
    {
        $this->hoversPercentage = $hoversPercentage;
    
        return $this;
    }

    /**
     * Get hoversPercentage
     *
     * @return float 
     */
    public function getHoversPercentage()
    {
        return $this->hoversPercentage;
    }

    /**
     * Set tagsHoversPercentage
     *
     * @param float $tagsHoversPercentage
     * @return Photo
     */
    public function setTagsHoversPercentage($tagsHoversPercentage)
    {
        $this->tagsHoversPercentage = $tagsHoversPercentage;
    
        return $this;
    }

    /**
     * Get tagsHoversPercentage
     *
     * @return float 
     */
    public function getTagsHoversPercentage()
    {
        return $this->tagsHoversPercentage;
    }

    /**
     * Set tagsClicksPercentage
     *
     * @param float $tagsClicksPercentage
     * @return Photo
     */
    public function setTagsClicksPercentage($tagsClicksPercentage)
    {
        $this->tagsClicksPercentage = $tagsClicksPercentage;
    
        return $this;
    }

    /**
     * Get tagsClicksPercentage
     *
     * @return float 
     */
    public function getTagsClicksPercentage()
    {
        return $this->tagsClicksPercentage;
    }

    /**
     * Add categories
     *
     * @param \AdEntify\CoreBundle\Entity\Category $categories
     * @return Photo
     */
    public function addCategorie(\AdEntify\CoreBundle\Entity\Category $categories)
    {
        $this->categories[] = $categories;
    
        return $this;
    }

    /**
     * Remove categories
     *
     * @param \AdEntify\CoreBundle\Entity\Category $categories
     */
    public function removeCategorie(\AdEntify\CoreBundle\Entity\Category $categories)
    {
        $this->categories->removeElement($categories);
    }

    /**
     * Add favoritesUsers
     *
     * @param \AdEntify\CoreBundle\Entity\User $favoritesUsers
     * @return Photo
     */
    public function addFavoritesUser(\AdEntify\CoreBundle\Entity\User $favoritesUsers)
    {
        $this->favoritesUsers[] = $favoritesUsers;
    
        return $this;
    }

    /**
     * Remove favoritesUsers
     *
     * @param \AdEntify\CoreBundle\Entity\User $favoritesUsers
     */
    public function removeFavoritesUser(\AdEntify\CoreBundle\Entity\User $favoritesUsers)
    {
        $this->favoritesUsers->removeElement($favoritesUsers);
    }
}