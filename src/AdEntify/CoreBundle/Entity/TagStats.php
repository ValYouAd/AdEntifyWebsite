<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 04/06/2013
 * Time: 17:59
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * TagStats
 *
 * @Serializer\XmlRoot("tagStat")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="tag_stats", indexes={@ORM\Index(name="search_idx", columns={"stat_type","ip_address","created_at","platform"})})
 * @ORM\Entity
 */
class TagStats
{
    const TYPE_HOVER = 'hover';
    const TYPE_CLICK = 'click';

    const PLATFORM_ADENTIFY_WEB = 'adentify-web';
    const PLATFORM_ADENTIFY_EMEBED = 'adentify-embed';
    const PLATFORM_ADENTIFY_WORDPRESS = 'adentify-wordpress';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="stat_type", type="string", length=25)
     */
    private $type = self::TYPE_HOVER;

    /**
     * @var datetime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var string
     *
     * @ORM\Column(name="ip_address", type="string", length=50)
     */
    private $ipAddress;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\Tag", inversedBy="stats")
     * @ORM\JoinColumn(nullable=false)
     */
    private $tag;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\User", inversedBy="stats")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="platform", type="string", length=100)
     */
    private $platform = self::PLATFORM_ADENTIFY_WEB;

    /**
     * @return int
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
     * @param string $platform
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;
        return $this;
    }

    /**
     * @return string
     */
    public function getPlatform()
    {
        return $this->platform;
    }
}