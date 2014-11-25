<?php
namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * ProductRetailer
 *
 * @Serializer\XmlRoot("product-retailer")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="product_retailers", indexes={@ORM\Index(name="search_idx", columns={"name"})})
 * @ORM\Entity
 */
class ProductRetailer
{
    /**
     * @Serializer\Groups({"details", "list", "slight-list"})
     *
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @Serializer\Groups({"details", "list", "slight-list"})
     *
     * @var integer
     *
     * @ORM\Column(name="provider_id", type="bigint", nullable=true)
     */
    private $providerId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     * @Serializer\Groups({"me", "details" , "list", "slight-list"})
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="deep_link_support", type="boolean", nullable=true)
     * @Serializer\Groups({"me", "details"})
     */
    private $deeplinkSupport;

    /**
     * @var string
     *
     * @ORM\Column(name="host_domain", type="string", length=255, nullable=true)
     * @Serializer\Groups({"me", "details" , "list", "slight-list"})
     */
    private $hostDomain;

    /**
     * @var bool
     *
     * @ORM\Column(name="mobile_optimized", type="boolean", nullable=true)
     * @Serializer\Groups({"me", "details"})
     */
    private $mobileOptimized;

    /**
     *
     * @var string
     *
     * @ORM\Column(name="logo", type="string", length=255, nullable=true)
     * @Serializer\Groups({"me", "details" , "list", "slight-list"})
     */
    private $logo;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isDeeplinkSupport()
    {
        return $this->deeplinkSupport;
    }

    /**
     * @param boolean $deeplinkSupport
     */
    public function setDeeplinkSupport($deeplinkSupport)
    {
        $this->deeplinkSupport = $deeplinkSupport;

        return $this;
    }

    /**
     * @return string
     */
    public function getHostDomain()
    {
        return $this->hostDomain;
    }

    /**
     * @param string $hostDomain
     */
    public function setHostDomain($hostDomain)
    {
        $this->hostDomain = $hostDomain;

        return $this;
    }

    /**
     * @return boolean
     */
    public function isMobileOptimized()
    {
        return $this->mobileOptimized;
    }

    /**
     * @param boolean $mobileOptimized
     */
    public function setMobileOptimized($mobileOptimized)
    {
        $this->mobileOptimized = $mobileOptimized;

        return $this;
    }

    /**
     * @return string
     */
    public function getLogo()
    {
        return $this->logo;
    }

    /**
     * @param string $logo
     */
    public function setLogo($logo)
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return int
     */
    public function getProviderId()
    {
        return $this->providerId;
    }

    /**
     * @param int $providerId
     */
    public function setProviderId($providerId)
    {
        $this->providerId = $providerId;
        return $this;
    }
}