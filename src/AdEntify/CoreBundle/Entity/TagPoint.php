<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 16/01/2014
 * Time: 17:20
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Tag Point
 *
 * @Serializer\XmlRoot("tag-point")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="tag_points", indexes={@ORM\Index(name="search_idx", columns={"status"})})
 * @ORM\Entity
 */
class TagPoint
{
    const STATUS_WAITING = 'waiting';
    const STATUS_CREDITED = 'credited';

    const TYPE_TAG_OWNER = 'tag-owner';
    const TYPE_TAG_AND_PHOTO_OWNER = 'tag-and-photo-owner';
    const TYPE_PHOTO_OWNER = 'photo-owner';

    const TAG_POINTS_PLACE = 10;
    const TAG_POINTS_PRODUCT = 10;
    const TAG_POINTS_COMPLETE_PRODUCT = 15;
    const TAG_POINTS_PERSON = 10;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Serializer\Exclude
     *
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\Tag", inversedBy="points", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $tag;

    /**
     * @Serializer\Exclude
     *
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\User", inversedBy="tagPoints")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @var datetime $created_on
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @Assert\DateTime()
     */
    private $createdAt;

    /**
     * @var datetime $creditedAt

     * @ORM\Column(name="credited_at", type="datetime", nullable=true)
     * @Assert\DateTime()
     */
    private $creditedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=50)
     */
    private $status = self::STATUS_WAITING;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=50)
     */
    private $type = self::TYPE_TAG_OWNER;

    /**
     * @var integer
     *
     * @ORM\Column(name="points", type="integer")
     */
    private $points = 0;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @param \AdEntify\CoreBundle\Entity\datetime $creditedAt
     */
    public function setCreditedAt($creditedAt)
    {
        $this->creditedAt = $creditedAt;
        return $this;
    }

    /**
     * @return \AdEntify\CoreBundle\Entity\datetime
     */
    public function getCreditedAt()
    {
        return $this->creditedAt;
    }

    /**
     * @param int $points
     */
    public function setPoints($points)
    {
        $this->points = $points;
        return $this;
    }

    /**
     * @return int
     */
    public function getPoints()
    {
        return $this->points;
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
     * @param mixed $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
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
} 