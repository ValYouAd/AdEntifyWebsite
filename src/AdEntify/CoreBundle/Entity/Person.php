<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 18/04/2013
 * Time: 15:15
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Person
 *
 * @Serializer\XmlRoot("person")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="people", indexes={@ORM\Index(name="search_idx", columns={"firstname", "lastname", "facebook_id"})})
 * @ORM\Entity(repositoryClass="AdEntify\CoreBundle\Entity\PersonRepository")
 */
class Person
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
     * @ORM\Column(name="firstname", type="string", length=255, nullable=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255, nullable=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $lastname;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="profile_picture_url", type="string", length=255, nullable=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $profilePictureUrl;

    /**
     * @var datetime $created_on
     *
     * @Serializer\Exclude
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @Assert\DateTime()
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $createdAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="facebook_id", type="bigint", nullable=true, unique=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $facebookId;

    /**
     * @var integer
     *
     * @ORM\Column(name="instagram_id", type="bigint", nullable=true, unique=true)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $instagramId;

    /**
     * @ORM\Column(name="gender", type="string", length=20, nullable=false)
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $gender = User::GENDER_UNKNOWN;

    /**
     * @var User
     *
     * @ORM\OneToOne(targetEntity="AdEntify\CoreBundle\Entity\User", mappedBy="person")
     * @Serializer\Groups({"details"})
     */
    private $user;

    /**
     * @Serializer\Exclude
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Tag", mappedBy="person")
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
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\Product", inversedBy="owners")
     * @ORM\OrderBy({"createdAt" = "ASC"})
     */
    private $products;

    /**
     * @var integer
     *
     * @ORM\Column(name="products_count", type="integer")
     * @Serializer\Groups({"details", "list"})
     */
    private $productsCount = 0;

    /**
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\User", mappedBy="friends")
     * @Serializer\Groups({"details"})
     */
    private $friends;

    /**
     * @Serializer\Exclude
     *
     * @var
     *
     * @ORM\Column(name="cost_per_tag", type="decimal", scale=4, precision=15)
     */
    private $costPerTag = 0;

    /**
     * @Serializer\Exclude
     *
     * @var
     *
     * @ORM\Column(name="adentify_fees", type="decimal", scale=4, precision=15)
     */
    private $adentifyFees = 50;

    /**
     * @Serializer\Exclude
     *
     * @var integer
     *
     * * @ORM\Column(name="tag_required_addict_reward", type="integer")
     */
    private $tagsRequiredAddictReward = 20;

    /**
     * @Serializer\Exclude
     *
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Reward", mappedBy="person")
     */
    private $rewards;

    public function __construct()
    {
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
        $this->friends = new \Doctrine\Common\Collections\ArrayCollection();
        $this->rewards = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
        return $this;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
        return $this;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    public function getFullName()
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    /**
     * @param \AdEntify\CoreBundle\Entity\datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return \AdEntify\CoreBundle\Entity\datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param int $facebookId
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;
        return $this;
    }

    /**
     * @return int
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    public function setGender($gender)
    {
        $this->gender = $gender;
        return $this;
    }

    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @return int
     */
    public function getTagsCount()
    {
        return $this->tagsCount;
    }

    public function addTag(\AdEntify\CoreBundle\Entity\Tag $tag)
    {
        $this->tags[] = $tag;
        $tag->setPhoto($this);
        return $this;
    }

    public function removeTag(\AdEntify\CoreBundle\Entity\Tag $tag)
    {
        $this->tags->removeElement($tag);
        $tag->setPhoto(null);
    }

    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param \AdEntify\CoreBundle\Entity\User $user
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return \AdEntify\CoreBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return int
     */
    public function getProductsCount()
    {
        return $this->productsCount;
    }

    public function addProduct(\AdEntify\CoreBundle\Entity\Product $product)
    {
        $product->addOwner($this);
        $this->products[] = $product;
        return $this;
    }

    public function removeProduct(\AdEntify\CoreBundle\Entity\Product $product)
    {
        $product->removeOwner($this);
        $this->products->removeElement($product);
    }

    public function getProducts()
    {
        return $this->products;
    }

    public function addFriend(\AdEntify\CoreBundle\Entity\User $friend)
    {
        $this->friends[] = $friend;
        return $this;
    }

    public function removeFriend(\AdEntify\CoreBundle\Entity\User $friend)
    {
        $this->friends->removeElement($friend);
    }

    public function getFriends()
    {
        return $this->friends;
    }

    /**
     * @param int $instagramId
     */
    public function setInstagramId($instagramId)
    {
        $this->instagramId = $instagramId;
        return $this;
    }

    /**
     * @return int
     */
    public function getInstagramId()
    {
        return $this->instagramId;
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
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $profilePictureUrl
     */
    public function setProfilePictureUrl($profilePictureUrl)
    {
        $this->profilePictureUrl = $profilePictureUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getProfilePictureUrl()
    {
        return $this->profilePictureUrl;
    }

    /**
     * @param mixed $costPerTag
     */
    public function setCostPerTag($costPerTag)
    {
        $this->costPerTag = $costPerTag;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCostPerTag()
    {
        return $this->costPerTag;
    }

    /**
     * @param mixed $adentifyFees
     */
    public function setAdentifyFees($adentifyFees)
    {
        $this->adentifyFees = $adentifyFees;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdentifyFees()
    {
        return $this->adentifyFees;
    }

    /**
     * @param int $tagsRequiredAddictReward
     */
    public function setTagsRequiredAddictReward($tagsRequiredAddictReward)
    {
        $this->tagsRequiredAddictReward = $tagsRequiredAddictReward;
        return $this;
    }

    /**
     * @return int
     */
    public function getTagsRequiredAddictReward()
    {
        return $this->tagsRequiredAddictReward;
    }

    public function addReward(Reward $reward)
    {
        $this->rewards[] = $reward;
        $reward->setPerson($this);
        return $this;
    }

    public function removeReward(Reward $reward)
    {
        $this->rewards->removeElement($reward);
        $reward->setPerson(null);
    }

    public function getRewards()
    {
        return $this->rewards;
    }
}