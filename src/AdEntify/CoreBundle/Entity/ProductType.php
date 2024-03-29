<?php

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * ProductType
 *
 * @Serializer\XmlRoot("product-type")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="product_types")
 * @ORM\Entity
 * @Gedmo\TranslationEntity(class="AdEntify\CoreBundle\Entity\ProductTypeTranslation")
 */
class ProductType
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $id;

    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="ProductType", inversedBy="children")
     * @Serializer\Groups({"details", "list"})
     */
    private $parent;

    /**
     * @ORM\OneToMany(targetEntity="ProductType", mappedBy="parent")
     * @Serializer\Groups({"details", "list"})
     */
    private $children;

    /**
     * @ORM\OneToMany(
     *     targetEntity="AdEntify\CoreBundle\Entity\ProductTypeTranslation",
     *  mappedBy="object",
     *  cascade={"persist", "remove"}
     * )
     */
    private $translations;

    /**
     * @Serializer\Exclude
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Tag", mappedBy="productType")
     * @ORM\OrderBy({"createdAt" = "ASC"})
     */
    private $tags;

    /**
     * @var integer
     *
     * @ORM\Column(name="tags_count", type="integer")
     * @Serializer\Groups({"details", "list"})
     */
    private $tagsCount = 0;

    /**
     * @Serializer\Exclude
     *
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Reward", mappedBy="productType")
     */
    private $rewards;

    public function __construct()
    {
        $this->children = new \Doctrine\Common\Collections\ArrayCollection();
        $this->translations = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->rewards = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set name
     *
     * @param string $name
     * @return ProductType
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

    public function setParent(ProductType $parent)
    {
        $this->parent = $parent;
    }

    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @return mixed
     */
    public function getChildren()
    {
        return $this->children;
    }

    public function addTranslation(ProductTypeTranslation $t)
    {
        if (!$this->translations->contains($t)) {
            $this->translations[] = $t;
            $t->setObject($this);
        }

        return $this;
    }

    public function removeTranslation(ProductTypeTranslation $translations)
    {
        $this->translations->removeElement($translations);
    }

    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @return int
     */
    public function getTagsCount()
    {
        return $this->tagsCount;
    }

    /**
     * @param int $tagsCount
     */
    public function setTagsCount($tagsCount)
    {
        $this->tagsCount = $tagsCount;
        return $this;
    }

    public function addTag(\AdEntify\CoreBundle\Entity\Tag $tag)
    {
        $this->tags[] = $tag;
        $tag->setProduct($this);
        return $this;
    }

    public function removeTag(\AdEntify\CoreBundle\Entity\Tag $tag)
    {
        $this->tags->removeElement($tag);
        $tag->setProduct(null);
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function addReward(Reward $reward)
    {
        $this->rewards[] = $reward;
        $reward->setProductType($this);
        return $this;
    }

    public function removeReward(Reward $reward)
    {
        $this->rewards->removeElement($reward);
        $reward->setProductType(null);
    }

    public function getRewards()
    {
        return $this->rewards;
    }
}
