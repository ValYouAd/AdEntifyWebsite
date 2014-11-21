<?php

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * UserProductProvider
 *
 * @Serializer\XmlRoot("user-product-provider")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="user_product_provider", indexes={@ORM\Index(name="search_idx", columns={"user"})})
 * @ORM\Entity
 */
class UserProductProvider
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
     * @var string
     *
     * @ORM\Column(name="provider_key", type="string", length=255)
     * @Serializer\Groups({"me", "details" , "list", "slight-list"})
     */
    private $providerKey;

    /**
     * @var integer
     *
     * @ORM\Column(name="user", type="string", length=255)
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\User", mappedBy="productProviders")
     * @Serializer\Groups({"me", "details" , "list", "slight-list"})
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="api_key", type="string", length=255)
     * @Serializer\Groups({"me", "details" , "list", "slight-list"})
     */
    private $apiKey;
    
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
     * Set providerKey
     *
     * @param string $providerKey
     * @return UserProductProvider
     */
    public function setProviderKey($providerKey)
    {
        $this->providerKey = $providerKey;
    
        return $this;
    }

    /**
     * Get providerKey
     *
     * @return string 
     */
    public function getProviderKey()
    {
        return $this->providerKey;
    }

    /**
     * Set user
     *
     * @param string $user
     * @return UserProductProvider
     */
    public function setUser($user)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return string 
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set apiKey
     *
     * @param string $apiKey
     * @return UserProductProvider
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    
        return $this;
    }

    /**
     * Get apiKey
     *
     * @return string 
     */
    public function getApiKey()
    {
        return $this->apiKey;
    }
}