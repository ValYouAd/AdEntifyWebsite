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
 * @ORM\Table(name="product_providers", indexes={@ORM\Index(name="search_idx", columns={"providerKey"})})
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
}