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
 * @ORM\Entity(repositoryClass="AdEntify\CoreBundle\Entity\DeviceRepository")
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
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=10, nullable=true)
     * @Serializer\Groups({"details"})
     */
    private $locale;

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

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @param Device $device
     */
    public function fillFromExisting(Device $device, User $user)
    {
        $this->setAppVersion($device->getAppVersion());
        $this->setOperatingSystem($device->getOperatingSystem());
        $this->setPlatform($device->getPlatform());
        $this->setToken($device->getToken());
        $this->setOwner($user);
        $this->setLocale($user->getLocale());
    }
}
