<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 16/04/2013
 * Time: 12:10
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Like
 *
 * @Serializer\XmlRoot("like")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="likes")
 * @ORM\Entity
 */
class Like
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
     * @var datetime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @Assert\DateTime()
     */
    private $created_at;

    /**
     * @var datetime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     * @Assert\DateTime()
     */
    private $deleted_at;

    /**
     * @Serializer\Exclude
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\Photo", inversedBy="likes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $photo;

    /**
     * @Serializer\Exclude
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\User", inversedBy="likes")
     */
    private $liker;

    /**
     * @var string
     *
     * @ORM\Column(name="ip_address", type="string", length=50)
     */
    private $ipAddress;

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
     * @param \AdEntify\CoreBundle\Entity\datetime $created_at
     * @return Like
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * @return \AdEntify\CoreBundle\Entity\datetime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @param \AdEntify\CoreBundle\Entity\datetime $deleted_at
     */
    public function setDeletedAt($deleted_at)
    {
        $this->deleted_at = $deleted_at;
        return $this;
    }

    /**
     * @return \AdEntify\CoreBundle\Entity\datetime
     */
    public function getDeletedAt()
    {
        return $this->deleted_at;
    }

    public function setPhoto($photo)
    {
        $this->photo = $photo;
        return $this;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setLiker($liker)
    {
        $this->liker = $liker;
        return $this;
    }

    public function getLiker()
    {
        return $this->liker;
    }

    /**
     * @param string $ipAddress
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    /**
     * @return string
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }
}