<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 19/07/2013
 * Time: 12:55
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Notification
 *
 * @Serializer\XmlRoot("notification")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="notifications", indexes={@ORM\Index(name="search_idx", columns={"status","object_id","object_type"})})
 * @ORM\Entity
 */
class Notification
{
    const STATUS_UNREAD = 'unread';
    const STATUS_READ =   'read';

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
     * @var datetime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @Assert\DateTime()
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=50)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $status = self::STATUS_UNREAD;

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
     * @Serializer\Exclude
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\User", inversedBy="notifications")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\User")
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $author;

    /**
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\Photo")
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $photos;

    /**
     * @var int
     *
     * @ORM\Column(name="object_id", type="bigint", nullable=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $objectId;

    /**
     * @var string
     *
     * @ORM\Column(name="object_type", type="string", length=100, nullable=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $objectType;

    /**
     * @var string
     *
     * @ORM\Column(name="notification_type", type="string", length=100, nullable=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\Brand", inversedBy="notifications")
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $brand;

    public function __construct()
    {
        $this->photos = new \Doctrine\Common\Collections\ArrayCollection();
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param int $objectId
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;
        return $this;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * @param string $objectType
     */
    public function setObjectType($objectType)
    {
        $this->objectType = $objectType;
        return $this;
    }

    /**
     * @return string
     */
    public function getObjectType()
    {
        return $this->objectType;
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
     * @param string $status
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
     * @param mixed $author
     */
    public function setAuthor($author)
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
     * @param string $messageOptions
     */
    public function setMessageOptions($messageOptions)
    {
        $this->messageOptions = $messageOptions;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessageOptions()
    {
        return $this->messageOptions;
    }

    /**
     * @return mixed
     */
    public function getPhotos()
    {
        return $this->photos;
    }

    public function addPhoto(Photo $photo)
    {
        $this->photos->add($photo);
        return $this;
    }

    public function removePhoto(Photo $photo)
    {
        $this->photos->removeElement($photo);
        return $this;
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
}