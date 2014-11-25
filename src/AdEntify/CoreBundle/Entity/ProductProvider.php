<?php

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * ProductProvider
 *
 * @Serializer\XmlRoot("product-provider")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="product_providers", indexes={@ORM\Index(name="search_idx", columns={"provider_key"})})
 * @ORM\Entity
 */
class ProductProvider
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
     * @var string
     *
     * @ORM\Column(name="provider_name", type="string", length=255)
     * @Serializer\Groups({"me", "details" , "list", "slight-list"})
     */
    private $name;

    /**
     *
     * @Serializer\Exclude
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\UserProductProvider", mappedBy="productProviders", cascade={"persist", "remove"})
     */
    private $users;

    /**
     * @return string
     */
    public function getProviderKey()
    {
        return $this->providerKey;
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
     * @param string $providerKey
     */
    public function setProviderKey($providerKey)
    {
        $this->providerKey = $providerKey;

        return $this;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return ProductProvider
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
     * Constructor
     */
    public function __construct()
    {
        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add users
     *
     * @param \AdEntify\CoreBundle\Entity\UserProductProvider $users
     * @return ProductProvider
     */
    public function addUser(\AdEntify\CoreBundle\Entity\UserProductProvider $users)
    {
        $this->users[] = $users;
    
        return $this;
    }

    /**
     * Remove users
     *
     * @param \AdEntify\CoreBundle\Entity\UserProductProvider $users
     */
    public function removeUser(\AdEntify\CoreBundle\Entity\UserProductProvider $users)
    {
        $this->users->removeElement($users);
    }

    /**
     * Get users
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getUsers()
    {
        return $this->users;
    }
}