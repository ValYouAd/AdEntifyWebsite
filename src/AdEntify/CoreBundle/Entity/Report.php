<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 14/11/2013
 * Time: 17:26
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Report
 *
 * @Serializer\XmlRoot("report")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="reports")
 * @ORM\Entity
 */
class Report
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\User")
     *
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $user;

    /**
     * @var datetime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @Assert\DateTime()
     *
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\Tag", inversedBy="reports")
     *
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $tag;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\Photo", inversedBy="reports")
     *
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $photo;

    /**
     * @var string
     *
     * @ORM\Column(name="reason", type="text", nullable=true)
     *
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $reason;

    /**
     * @var string
     *
     * @ORM\Column(name="reason_option", type="text", nullable=true)
     *
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $option;

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
     * @param mixed $photo
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param string $reason
     */
    public function setReason($reason)
    {
        $this->reason = $reason;
        return $this;
    }

    /**
     * @return string
     */
    public function getReason()
    {
        return $this->reason;
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
     * @param string $option
     */
    public function setOption($option)
    {
        $this->option = $option;
    }

    /**
     * @return string
     */
    public function getOption()
    {
        return $this->option;
    }
}