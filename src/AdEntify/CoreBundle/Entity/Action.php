<?php

namespace AdEntify\CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Action
 *
 * @Serializer\XmlRoot("action")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="actions", indexes={@ORM\Index(name="search_idx", columns={"action_type","linked_object_id","linked_object_type"})})
 * @ORM\Entity(repositoryClass="AdEntify\CoreBundle\Entity\ActionRepository")
 */
class Action
{
    const TYPE_PHOTO_UPLOAD = 'photo-upload';
    const TYPE_PHOTO_COMMENT = 'photo-comment';
    const TYPE_PHOTO_LIKE = 'photo-like';
    const TYPE_PHOTO_FAVORITE = 'photo-fav';
    const TYPE_PHOTO_TAG = 'photo-tag';
    const TYPE_PHOTO_BRAND_TAG = 'photo-brand-tag';
    const TYPE_REWARD_NEW = 'reward-new';
    const TYPE_USER_FOLLOW = 'user-follow';
    const TYPE_USER_POINTS = 'user-points';
    const TYPE_BRAND_FOLLOW = 'brand-follow';

    const VISIBILITY_PUBLIC = 'public';
    const VISIBILITY_FRIENDS = 'friends';

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
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text")
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $message;

    /**
     * @var string
     *
     * @ORM\Column(name="message_options", type="array", nullable=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $messageOptions;

    /**
     * @var integer
     *
     * @ORM\Column(name="linked_object_id", type="integer", nullable=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $linkedObjectId;

    /**
     * @var string
     *
     * @ORM\Column(name="linked_object_type", type="string", length=255, nullable=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $linkedObjectType;

    /**
     * @var string
     *
     * @ORM\Column(name="action_type", type="string", length=255)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\User", inversedBy="actions")
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $target;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $author;

    /**
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\Photo", cascade={"remove"})
     * @Serializer\Groups({"details", "list", "slight-list"})
     * @ORM\JoinTable(joinColumns={@ORM\JoinColumn(onDelete="CASCADE")},
     *      inverseJoinColumns={@ORM\JoinColumn(onDelete="CASCADE")}
     * )
     */
    private $photos;

    /**
     * @var string
     *
     * @ORM\Column(name="visibility", type="string", length=255)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $visibility = self::VISIBILITY_PUBLIC;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\Brand", inversedBy="notifications")
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $brand;

    public function __construct()
    {
        $this->photos = new ArrayCollection();
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
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Action
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set message
     *
     * @param string $message
     * @return Action
     */
    public function setMessage($message)
    {
        $this->message = $message;
    
        return $this;
    }

    /**
     * Get message
     *
     * @return string 
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set messageOptions
     *
     * @param string $messageOptions
     * @return Action
     */
    public function setMessageOptions($messageOptions)
    {
        $this->messageOptions = $messageOptions;
    
        return $this;
    }

    /**
     * Get messageOptions
     *
     * @return string 
     */
    public function getMessageOptions()
    {
        return $this->messageOptions;
    }

    /**
     * Set linkedObjectId
     *
     * @param integer $linkedObjectId
     * @return Action
     */
    public function setLinkedObjectId($linkedObjectId)
    {
        $this->linkedObjectId = $linkedObjectId;
    
        return $this;
    }

    /**
     * Get linkedObjectId
     *
     * @return integer 
     */
    public function getLinkedObjectId()
    {
        return $this->linkedObjectId;
    }

    /**
     * Set linkedObjectType
     *
     * @param string $linkedObjectType
     * @return Action
     */
    public function setLinkedObjectType($linkedObjectType)
    {
        $this->linkedObjectType = $linkedObjectType;
    
        return $this;
    }

    /**
     * Get linkedObjectType
     *
     * @return string 
     */
    public function getLinkedObjectType()
    {
        return $this->linkedObjectType;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Action
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param User $author
     * @return $this
     */
    public function setAuthor(User $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param User $target
     * @return $this
     */
    public function setTarget(User $target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return mixed
     */
    public function getPhotos()
    {
        return $this->photos;
    }

    /**
     * @param Photo $photo
     * @return $this
     */
    public function addPhoto(Photo $photo)
    {
        $this->photos->add($photo);
        return $this;
    }

    /**
     * @param Photo $photo
     * @return $this
     */
    public function removePhoto(Photo $photo)
    {
        $this->photos->removeElement($photo);
        return $this;
    }

    /**
     * @param $visibility
     * @return $this
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @param mixed $brand
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBrand()
    {
        return $this->brand;
    }

    public static function getVisibilityWithPhotoVisibility($photoVisibility)
    {
        switch ($photoVisibility) {
            case Photo::SCOPE_PUBLIC:
                return  Action::VISIBILITY_PUBLIC;
            case Photo::SCOPE_PRIVATE:
                return Action::VISIBILITY_FRIENDS;
            default:
                return Action::VISIBILITY_PUBLIC;
        }
    }
}
