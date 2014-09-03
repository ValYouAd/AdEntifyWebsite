<?php

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Device
 *
 * @Serializer\XmlRoot("device")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="devices")
 * @ORM\Entity
 */
class Device
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"details"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255)
     *
     * @Serializer\Groups({"details"})
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\Column(name="platform", type="string", length=255)
     *
     * @Serializer\Groups({"details"})
     */
    private $platform;

    /**
     * @var string
     *
     * @ORM\Column(name="operating_system", type="string", length=255)
     *
     * @Serializer\Groups({"details"})
     */
    private $operatingSystem;

    /**
     * @var string
     *
     * @ORM\Column(name="app_version", type="string", length=255)
     *
     * @Serializer\Groups({"details"})
     */
    private $appVersion;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\User", inversedBy="devices")
     * @Serializer\Exclude
     */
    private $owner;

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
     * Set token
     *
     * @param string $token
     * @return Device
     */
    public function setToken($token)
    {
        $this->token = $token;
    
        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Device
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set platform
     *
     * @param string $platform
     * @return Device
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
     * Set operatingSystem
     *
     * @param string $operatingSystem
     * @return Device
     */
    public function setOperatingSystem($operatingSystem)
    {
        $this->operatingSystem = $operatingSystem;
    
        return $this;
    }

    /**
     * Get operatingSystem
     *
     * @return string 
     */
    public function getOperatingSystem()
    {
        return $this->operatingSystem;
    }

    /**
     * Set appVersion
     *
     * @param string $appVersion
     * @return Device
     */
    public function setAppVersion($appVersion)
    {
        $this->appVersion = $appVersion;
    
        return $this;
    }

    /**
     * Get appVersion
     *
     * @return string 
     */
    public function getAppVersion()
    {
        return $this->appVersion;
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
}
