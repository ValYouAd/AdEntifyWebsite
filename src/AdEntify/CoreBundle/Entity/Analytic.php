<?php

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * Analytic
 *
 * @ORM\Table(name="analytics", indexes={@ORM\Index(name="search_idx", columns={"action","element","ip_address","created_at","platform"})})
 * @ORM\Entity(repositoryClass="AdEntify\CoreBundle\Entity\AnalyticRepository")
 */
class Analytic
{
    const ACTION_VIEW = 'view';
    const ACTION_HOVER = 'hover';
    const ACTION_CLICK = 'click';
    const ACTION_INTERACTION = 'interaction';

    const ELEMENT_PHOTO = 'photo';
    const ELEMENT_TAG = 'tag';

    const PLATFORM_ADENTIFY_WEB = 'web';
    const PLATFORM_ADENTIFY_EMBED = 'embed';
    const PLATFORM_ADENTIFY_WORDPRESS = 'wordpress';
    const PLATFORM_ADENTIFY_IPHONE = 'iphone';

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
     * @ORM\Column(name="action", type="string", length=50)
     */
    private $action;

    /**
     * @var string
     *
     * @ORM\Column(name="action_value", type="integer", nullable=true)
     */
    private $actionValue;

    /**
     * @var string
     *
     * @ORM\Column(name="element", type="string", length=50)
     */
    private $element;

    /**
     * @var \DateTime
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
     * @var string
     *
     * @ORM\Column(name="platform", type="string", length=100)
     */
    private $platform;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="text", nullable=true)
     */
    private $link;

    /**
     * @var string
     *
     * @ORM\Column(name="source_url", type="text", nullable=true)
     */
    private $sourceUrl;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\Photo", inversedBy="analytics")
     */
    private $photo;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\Tag", inversedBy="analytics")
     */
    private $tag;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\User", inversedBy="analytics")
     */
    private $user;

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
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return string
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * @param string $element
     */
    public function setElement($element)
    {
        $this->element = $element;
        return $this;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Analytic
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
     * Set ipAddress
     *
     * @param string $ipAddress
     * @return Analytic
     */
    public function setIpAddress($ipAddress)
    {
        $this->ipAddress = $ipAddress;
    
        return $this;
    }

    /**
     * Get ipAddress
     *
     * @return string 
     */
    public function getIpAddress()
    {
        return $this->ipAddress;
    }

    /**
     * Set platform
     *
     * @param string $platform
     * @return Analytic
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;
    
        return $this;
    }

    /**
     * Get platform
     *
     * @return string 
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Set link
     *
     * @param string $link
     * @return Analytic
     */
    public function setLink($link)
    {
        $this->link = $link;
    
        return $this;
    }

    /**
     * Get link
     *
     * @return string 
     */
    public function getLink()
    {
        return $this->link;
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
     * @return string
     */
    public function getActionValue()
    {
	return $this->actionValue;
    }

    /**
     * @param string $actionValue
     */
    public function setActionValue($actionValue)
    {
	$this->actionValue = $actionValue;
	return $this;
    }

    /**
     * @return string
     */
    public function getSourceUrl()
    {
	return $this->sourceUrl;
    }

    /**
     * @param string $sourceUrl
     */
    public function setSourceUrl($sourceUrl)
    {
	$this->sourceUrl = $sourceUrl;
	return $this;
    }
}
