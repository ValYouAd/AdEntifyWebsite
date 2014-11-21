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
 * @ORM\Table(name="user_product_provider")
 * @ORM\Entity
 */
class UserProductProvider
{
    /**
     * @var integer
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\ProductProvider", inversedBy="users")
     * @ORM\JoinColumn(name="product_providers_id", referencedColumnName="id", nullable=false)
     * @Serializer\Groups({"me", "details" , "list"})
     */
    private $productProviders;

    /**
     * @var integer
     * @ORM\Id()
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\User", inversedBy="productProviders")
     * @ORM\JoinColumn(name="users_id", referencedColumnName="id", nullable=false)
     * @Serializer\Groups({"me", "details" , "list"})
     */
    private $users;

    /**
     * @var string
     *
     * @ORM\Column(name="api_key", type="string", length=255, nullable=true)
     * @Serializer\Groups({"me", "details" , "list"})
     */
    private $apiKey;

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

    /**
     * Set productProviders
     *
     * @param \AdEntify\CoreBundle\Entity\ProductProvider $productProviders
     * @return UserProductProvider
     */
    public function setProductProviders(\AdEntify\CoreBundle\Entity\ProductProvider $productProviders)
    {
        $this->productProviders = $productProviders;
    
        return $this;
    }

    /**
     * Get productProviders
     *
     * @return \AdEntify\CoreBundle\Entity\ProductProvider 
     */
    public function getProductProviders()
    {
        return $this->productProviders;
    }

    /**
     * Set users
     *
     * @param \AdEntify\CoreBundle\Entity\User $users
     * @return UserProductProvider
     */
    public function setUsers(\AdEntify\CoreBundle\Entity\User $users)
    {
        $this->users = $users;
    
        return $this;
    }

    /**
     * Get users
     *
     * @return \AdEntify\CoreBundle\Entity\User 
     */
    public function getUsers()
    {
        return $this->users;
    }
}