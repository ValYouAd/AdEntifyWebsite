<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 16/04/2013
 * Time: 12:09
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Tag
 *
 * @Serializer\XmlRoot("tag")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="tags", indexes={@ORM\Index(name="search_idx", columns={"tag_type","visible"})})
 * @ORM\Entity
 */
class Tag
{
    const TYPE_PLACE =   "place";
    const TYPE_PRODUCT = "product";
    const TYPE_PERSON =  "person";

    const VALIDATION_NONE =    "none";
    const VALIDATION_WAITING = "waiting";
    const VALIDATION_DENIED =  "denied";
    const VALIDATION_GRANTED = "granted";

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
     * @ORM\Column(name="tag_type", type="string", length=25)
     */
    private $type = self::TYPE_PRODUCT;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255)
     * @Assert\NotBlank(message="tag.title.notblank")
     */
    private $title;

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
     */
    private $createdAt;

    /**
     * @var datetime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    private $deletedAt;


    /**
     * @var boolean
     *
     * @ORM\Column(name="visible", type="boolean")
     */
    private $visible = true;

    /**
     * @var boolean
     *
     * @ORM\Column(name="censored", type="boolean")
     */
    private $censored = false;

    /**
     * @var boolean
     *
     * @ORM\Column(name="waiting_validation", type="boolean")
     */
    private $waitingValidation = false;

    /**
     * @var string
     *
     * @ORM\Column(name="validation_status", type="string", length=25)
     */
    private $validationStatus = self::VALIDATION_NONE;

    /**
     * @var decimal
     *
     * @ORM\Column(name="x_position", type="decimal", scale=8)
     */
    private $xPosition;

    /**
     * @var decimal
     *
     * @ORM\Column(name="y_position", type="decimal", scale=8)
     */
    private $yPosition;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\Photo", inversedBy="tags")
     */
    private $photo;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\Venue", inversedBy="tags")
     * @ORM\JoinColumn(nullable=true)
     */
    private $venue;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\Product", inversedBy="tags")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\Person", inversedBy="tags")
     */
    private $person;

    /**
     * @Serializer\Exclude
     *
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\TagStats", mappedBy="tag", cascade={"persist", "remove"})
     * @ORM\OrderBy({"createdAt" = "ASC"})
     */
    private $stats;

    /**
     * @Serializer\Exclude
     *
     * @var bool
     *
     * @ORM\Column(name="revenue_assigned", type="boolean")
     */
    private $revenueAssigned = false;

    /**
     * @Serializer\Exclude
     *
     * @ORM\OneToOne(targetEntity="AdEntify\CoreBundle\Entity\BrandTag", mappedBy="tag", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     */
    private $brandTag;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\User", inversedBy="tags")
     */
    private $owner;

    public function __construct()
    {
        $this->stats = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
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
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $link
     * @return Tag
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
     * @param \AdEntify\CoreBundle\Entity\decimal $xPosition
     */
    public function setXPosition($xPosition)
    {
        $this->xPosition = $xPosition;
        return $this;
    }

    /**
     * @return \AdEntify\CoreBundle\Entity\decimal
     */
    public function getXPosition()
    {
        return $this->xPosition;
    }

    /**
     * @param \AdEntify\CoreBundle\Entity\decimal $yPosition
     */
    public function setYPosition($yPosition)
    {
        $this->yPosition = $yPosition;
        return $this;
    }

    /**
     * @return \AdEntify\CoreBundle\Entity\decimal
     */
    public function getYPosition()
    {
        return $this->yPosition;
    }

    /**
     * @param $photo
     * @return Tag
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
        return $this;
    }

    public function getPhoto()
    {
        return $this->photo;
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
     * @param boolean $censored
     */
    public function setCensored($censored)
    {
        $this->censored = $censored;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getCensored()
    {
        return $this->censored;
    }

    public function setPerson($person)
    {
        $this->person = $person;
        return $this;
    }

    public function getPerson()
    {
        return $this->person;
    }

    public function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param string $validationStatus
     */
    public function setValidationStatus($validationStatus)
    {
        $this->validationStatus = $validationStatus;
        return $this;
    }

    /**
     * @return string
     */
    public function getValidationStatus()
    {
        return $this->validationStatus;
    }

    /**
     * @param boolean $waitingValidation
     */
    public function setWaitingValidation($waitingValidation)
    {
        $this->waitingValidation = $waitingValidation;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getWaitingValidation()
    {
        return $this->waitingValidation;
    }

    public function addStat(\AdEntify\CoreBundle\Entity\TagStats $stat)
    {
        $this->stats[] = $stat;
        $stat->setTag($this);
        return $this;
    }

    public function removeStat(\AdEntify\CoreBundle\Entity\TagStats $stat)
    {
        $this->stats->removeElement($stat);
        $stat->setTag(null);
    }

    public function getStats()
    {
        return $this->stats;
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

    /**
     * @param boolean $revenueAssigned
     */
    public function setRevenueAssigned($revenueAssigned)
    {
        $this->revenueAssigned = $revenueAssigned;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getRevenueAssigned()
    {
        return $this->revenueAssigned;
    }

    /**
     * @param mixed $owner
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param mixed $brandTag
     */
    public function setBrandTag($brandTag)
    {
        $this->brandTag = $brandTag;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBrandTag()
    {
        return $this->brandTag;
    }
}