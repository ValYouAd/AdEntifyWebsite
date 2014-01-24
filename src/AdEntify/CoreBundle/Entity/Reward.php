<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 20/01/2014
 * Time: 13:33
 */
namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Reward
 *
 * @Serializer\XmlRoot("reward")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="rewards", indexes={@ORM\Index(name="search_idx", columns={"type"})})
 * @ORM\Entity
 */
class Reward
{
    const TYPE_ADDICT = 'addict';
    const TYPE_GOLD = 'gold';
    const TYPE_SILVER = 'silver';
    const TYPE_BRONZE = 'bronze';

    const MAX_GOLD_FANS = 10;
    const MAX_SILVER_FANS = 20;
    const MAX_BRONZE_FANS = 30;

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
     * @ORM\Column(name="win_at", type="datetime")
     */
    private $winAt;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type = self::TYPE_ADDICT;

    /**
     * @var boolean
     *
     * @ORM\Column(name="can_loose", type="boolean")
     */
    private $canLoose = false;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\Venue", inversedBy="rewards")
     */
    private $venue;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\Product", inversedBy="rewards")
     */
    private $product;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\ProductType", inversedBy="rewards")
     */
    private $productType;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\Person", inversedBy="rewards")
     */
    private $person;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\Brand", inversedBy="rewards")
     */
    private $brand;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\User", inversedBy="rewards")
     */
    private $owner;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param boolean $canLoose
     */
    public function setCanLoose($canLoose)
    {
        $this->canLoose = $canLoose;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getCanLoose()
    {
        return $this->canLoose;
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
     * @param mixed $brand
     */
    public function setBrand(Brand $brand)
    {
        $this->brand = $brand;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param mixed $person
     */
    public function setPerson(Person $person)
    {
        $this->person = $person;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param mixed $product
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param mixed $productType
     */
    public function setProductType(ProductType $productType)
    {
        $this->productType = $productType;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProductType()
    {
        return $this->productType;
    }

    /**
     * @param mixed $venue
     */
    public function setVenue(Venue $venue)
    {
        $this->venue = $venue;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVenue()
    {
        return $this->venue;
    }

    /**
     * @param mixed $owner
     */
    public function setOwner(User $owner)
    {
        $this->owner = $owner;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * @param \AdEntify\CoreBundle\Entity\datetime $winAt
     */
    public function setWinAt($winAt)
    {
        $this->winAt = $winAt;
        return $this;
    }

    /**
     * @return \AdEntify\CoreBundle\Entity\datetime
     */
    public function getWinAt()
    {
        return $this->winAt;
    }
} 