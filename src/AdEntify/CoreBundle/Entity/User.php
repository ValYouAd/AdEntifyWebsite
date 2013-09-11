<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 16/04/2013
 * Time: 15:25
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity;

use AdEntify\CoreBundle\Entity\OAuth\Client;
use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * User
 *
 * @Serializer\XmlRoot("user")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Entity(repositoryClass="AdEntify\CoreBundle\Entity\UserRepository")
 * @ORM\Table(name="users", indexes={@ORM\Index(name="search_idx", columns={"firstname","lastname","email","facebook_id"})})
 */
class User extends BaseUser
{
    const GENDER_MALE =   "male";
    const GENDER_FEMALE = "female";
    const GENDER_UNKNOWN = "unknown";

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255, nullable=true)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255, nullable=true)
     */
    private $lastname;

    /**
     * @Serializer\Exclude
     * @var datetime $created_on
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @Assert\DateTime()
     */
    private $createdAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="facebook_id", type="bigint", nullable=true)
     */
    protected $facebookId;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook_username", type="text", nullable=true)
     */
    private $facebookUsername;

    /**
     * @Serializer\Exclude
     * @ORM\Column(name="facebook_access_token", type="string", length=255, nullable=true)
     */
    protected $facebookAccessToken;

    /**
     *
     * @var string
     *
     * @ORM\Column(name="twitter_id", type="string", length=255, nullable=true)
     */
    protected $twitterId;

    /**
     * @Serializer\Exclude
     * @ORM\Column(name="twitter_access_token", type="string", length=255, nullable=true)
     */
    protected $twitterAccessToken;

    /**
     * @ORM\Column(name="gender", type="string", length=20, nullable=true)
     */
    private $gender = self::GENDER_UNKNOWN;

    /**
     * @Serializer\Exclude
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Photo", mappedBy="owner")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $photos;

    /**
     * @var integer
     *
     * @ORM\Column(name="photos_count", type="integer")
     */
    private $photosCount = 0;

    /**
     * @Serializer\Exclude
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Like", mappedBy="liker")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $likes;

    /**
     * @Serializer\Exclude
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Comment", mappedBy="author")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $comments;

    /**
     * @var Person
     *
     * @ORM\OneToOne(targetEntity="AdEntify\CoreBundle\Entity\Person", inversedBy="user")
     */
    private $person;

    /**
     * @Serializer\Exclude
     * @var OAuthUserInfo
     *
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\OAuthUserInfo", mappedBy="user")
     */
    private $oAuthUserInfos;

    /**
     * @Serializer\Exclude
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\TagStats", mappedBy="user")
     * @ORM\OrderBy({"createdAt" = "ASC"})
     */
    private $stats;

    /**
     * @Serializer\Exclude
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\User", inversedBy="followers")
     * @ORM\JoinTable(name="users_followings",
     *      joinColumns={@ORM\JoinColumn(name="following_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="follower_id", referencedColumnName="id", unique=true)})
     */
    private $followings;

    /**
     * @Serializer\Exclude
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\User", mappedBy="followings")
     */
    private $followers;

    /**
     * @Serializer\Exclude
     * @var datetime $lastFriendsListUpdate
     *
     * @ORM\Column(name="last_friends_list_update", type="datetime", nullable=true)
     */
    private $lastFriendsListUpdate;

    /**
     * @Serializer\Exclude
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\Person", inversedBy="friends")
     * @ORM\JoinTable(name="user_friends")
     */
    private $friends;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=10, nullable=true)
     */
    private $locale;

    /**
     * @Serializer\Exclude
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\Photo", inversedBy="favoritesUsers")
     * @ORM\JoinTable(name="user_favorites_photos")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $favoritesPhotos;

    /**
     * @Serializer\Exclude
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Notification", mappedBy="owner")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $notifications;

    /**
     * @Serializer\Exclude
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\BrandTag", mappedBy="user")
     * @ORM\OrderBy({"createdAt" = "ASC"})
     */
    private $brandTags;

    /**
     * @Serializer\Exclude
     * @ORM\OneToMany(targetEntity="AdEntify\CoreBundle\Entity\Tag", mappedBy="owner")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    private $tags;

    /**
     * @Serializer\Exclude
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\OAuth\Client", inversedBy="users")
     */
    private $clients;

    public function __construct()
    {
        parent::__construct();

        $this->photos = new \Doctrine\Common\Collections\ArrayCollection();
        $this->likes = new \Doctrine\Common\Collections\ArrayCollection();
        $this->comments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->oAuthUserInfos = new \Doctrine\Common\Collections\ArrayCollection();
        $this->stats = new \Doctrine\Common\Collections\ArrayCollection();
        $this->followings = new \Doctrine\Common\Collections\ArrayCollection();
        $this->followers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->friends = new \Doctrine\Common\Collections\ArrayCollection();
        $this->favoritesPhotos = new \Doctrine\Common\Collections\ArrayCollection();
        $this->notifications = new \Doctrine\Common\Collections\ArrayCollection();
        $this->brandTags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->clients = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param \AdEntify\CoreBundle\Entity\datetime $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return \AdEntify\CoreBundle\Entity\datetime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param string $facebookId
     * @return void
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;
        if (empty($this->username))
        {
            $this->setUsername($facebookId);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    public function getFullname() {
        if (empty($this->firstname) || empty($this->lastname))
            return false;
        return $this->getFirstname() . ' ' . $this->getLastname();
    }

    /**
     * @param string $firstname
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;
    }

    /**
     * @return string
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string $lastname
     */
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;
    }

    /**
     * @return string
     */
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Sets the email.
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->setUsername($email);

        return parent::setEmail($email);
    }

    public function serialize()
    {
        return serialize(array($this->facebookId, parent::serialize()));
    }

    public function unserialize($data)
    {
        list($this->facebookId, $parentData) = unserialize($data);
        parent::unserialize($parentData);
    }

    /**
     * @param Array
     */
    public function setFBData($fbdata)
    {
        if (isset($fbdata['id'])) {
            $this->setFacebookId($fbdata['id']);
            $this->addRole('ROLE_FACEBOOK');
        }
        if (isset($fbdata['first_name'])) {
            $this->setFirstname($fbdata['first_name']);
        }
        if (isset($fbdata['last_name'])) {
            $this->setLastname($fbdata['last_name']);
        }
        if (isset($fbdata['email'])) {
            $this->setEmail($fbdata['email']);
        }
        if (isset($fbdata['username'])) {
            $this->setFacebookUsername($fbdata['username']);
        }
        if (isset($fbdata['gender'])) {
            if ($fbdata['gender'] == 'male')
                $this->setGender(self::GENDER_MALE);
            if ($fbdata['gender'] == 'female')
                $this->setGender(self::GENDER_FEMALE);
        }
    }

    public function addPhoto(\AdEntify\CoreBundle\Entity\Photo $photo)
    {
        $this->photos[] = $photo;
        $photo->setOwner($this);
        return $this;
    }

    public function removePhoto(\AdEntify\CoreBundle\Entity\Photo $photo)
    {
        $this->photos->removeElement($photo);
        $photo->setOwner(null);
    }

    public function getPhotos()
    {
        return $this->photos;
    }

    public function addComment(\AdEntify\CoreBundle\Entity\Comment $comment)
    {
        $this->comments[] = $comment;
        $comment->setAuthor($this);
        return $this;
    }

    public function removeComment(\AdEntify\CoreBundle\Entity\Comment $comment)
    {
        $this->comments->removeElement($comment);
        $comment->setAuthor(null);
    }

    public function getComments()
    {
        return $this->comments;
    }

    public function addLike(\AdEntify\CoreBundle\Entity\Like $like)
    {
        $this->likes[] = $like;
        $like->setLiker($this);
        return $this;
    }

    public function removeLike(\AdEntify\CoreBundle\Entity\Like $like)
    {
        $this->likes->removeElement($like);
        $like->setLiker(null);
    }

    public function getLikes()
    {
        return $this->likes;
    }

    /**
     * @param \AdEntify\CoreBundle\Entity\Person $person
     */
    public function setPerson($person)
    {
        $this->person = $person;
    }

    /**
     * @return \AdEntify\CoreBundle\Entity\Person
     */
    public function getPerson()
    {
        return $this->person;
    }

    /**
     * @param string $facebookUsername
     */
    public function setFacebookUsername($facebookUsername)
    {
        $this->facebookUsername = $facebookUsername;
    }

    /**
     * @return string
     */
    public function getFacebookUsername()
    {
        return $this->facebookUsername;
    }

    public function addOAuthUserInfo(\AdEntify\CoreBundle\Entity\OAuthUserInfo $oAuthUserInfo)
    {
        $this->oAuthUserInfos[] = $oAuthUserInfo;
        $oAuthUserInfo->setUser($this);
        return $this;
    }

    public function removeOAuthUserInfo(\AdEntify\CoreBundle\Entity\OAuthUserInfo $oAuthUserInfo)
    {
        $this->oAuthUserInfos->removeElement($oAuthUserInfo);
        $oAuthUserInfo->setUser(null);
    }

    public function getOAuthUserInfos()
    {
        return $this->oAuthUserInfos;
    }

    /**
     * @param string $twitterId
     */
    public function setTwitterId($twitterId)
    {
        $this->twitterId = $twitterId;
        if (empty($this->username))
        {
            $this->setUsername($twitterId);
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getTwitterId()
    {
        return $this->twitterId;
    }

    /**
     * @param string $twitterUsername
     */
    public function setTwitterUsername($twitterUsername)
    {
        $this->twitterUsername = $twitterUsername;
        return $this;
    }

    /**
     * @return string
     */
    public function getTwitterUsername()
    {
        return $this->twitterUsername;
    }

    public function addStat(\AdEntify\CoreBundle\Entity\TagStats $stat)
    {
        $this->stats[] = $stat;
        $stat->setUser($this);
        return $this;
    }

    public function removeStat(\AdEntify\CoreBundle\Entity\TagStats $stat)
    {
        $this->stats->removeElement($stat);
        $stat->setUser(null);
    }

    public function getStats()
    {
        return $this->stats;
    }

    public function addFollower(\AdEntify\CoreBundle\Entity\User $follower)
    {
        $follower->addFollowing($this);
        $this->followers[] = $follower;
        return $this;
    }

    public function removeFollower(\AdEntify\CoreBundle\Entity\User $follower)
    {
        $follower->removePhoto($this);
        $this->followers->removeElement($follower);
    }

    public function getFollowers()
    {
        return $this->followers;
    }

    public function addFollowing(\AdEntify\CoreBundle\Entity\User $following)
    {
        $this->followings[] = $following;
        return $this;
    }

    public function removeFollowing(\AdEntify\CoreBundle\Entity\User $following)
    {
        $this->followings->removeElement($following);
    }

    public function getFollowings()
    {
        return $this->followings;
    }

    /**
     * @param int $photosCount
     */
    public function setPhotosCount($photosCount)
    {
        $this->photosCount = $photosCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getPhotosCount()
    {
        return $this->photosCount;
    }

    /**
     * @param \AdEntify\CoreBundle\Entity\datetime $lastFriendsListUpdate
     */
    public function setLastFriendsListUpdate($lastFriendsListUpdate)
    {
        $this->lastFriendsListUpdate = $lastFriendsListUpdate;
        return $this;
    }

    /**
     * @return \AdEntify\CoreBundle\Entity\datetime
     */
    public function getLastFriendsListUpdate()
    {
        return $this->lastFriendsListUpdate;
    }

    public function addFriend(\AdEntify\CoreBundle\Entity\Person $friend)
    {
        $friend->addFriend($this);
        $this->friends[] = $friend;
        return $this;
    }

    public function removeFriend(\AdEntify\CoreBundle\Entity\Person $friend)
    {
        $friend->removeFriend($this);
        $this->friends->removeElement($friend);
    }

    public function getFriends()
    {
        return $this->friends;
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
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    public function addFavoritePhoto(\AdEntify\CoreBundle\Entity\Photo $photo)
    {
        $photo->addFavoriteUser($this);
        $this->favoritesPhotos[] = $photo;
        return $this;
    }

    public function removeFavoritePhoto(\AdEntify\CoreBundle\Entity\Photo $photo)
    {
        $photo->removeFavoriteUser($this);
        $this->favoritesPhotos->removeElement($photo);
    }

    public function getFavoritePhotos()
    {
        return $this->favoritesPhotos;
    }

    public function getFollowingsIds()
    {
        $followings = array();
        if (count($this->followings) > 0) {
            foreach($this->getFollowings() as $following) {
                $followings[] = $following->getId();
            }
        } else {
            $followings[] = 0;
        }
        return $followings;
    }

    public function addNotification(\AdEntify\CoreBundle\Entity\Notification $notification)
    {
        $this->notifications[] = $notification;
        $notification->setOwner($this);
        return $this;
    }

    public function removeNotification(\AdEntify\CoreBundle\Entity\Notification $notification)
    {
        $this->notifications->removeElement($notification);
        $notification->setOwner(null);
    }

    public function getNotifications()
    {
        return $this->notifications;
    }

    /**
     * @param mixed $twitterAccessToken
     */
    public function setTwitterAccessToken($twitterAccessToken)
    {
        $this->twitterAccessToken = $twitterAccessToken;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTwitterAccessToken()
    {
        return $this->twitterAccessToken;
    }

    public function addBrandTag(\AdEntify\CoreBundle\Entity\BrandTag $brandTag)
    {
        $this->brandTags[] = $brandTag;
        $brandTag->setUser($this);
        return $this;
    }

    public function removeBrandTag(\AdEntify\CoreBundle\Entity\BrandTag $brandTag)
    {
        $this->brandTags->removeElement($brandTag);
        $brandTag->setUser(null);
    }

    public function getBrandTags()
    {
        return $this->brandTags;
    }

    public function addTag(\AdEntify\CoreBundle\Entity\Tag $tag)
    {
        $this->tags[] = $tag;
        $tag->setOwner($this);
        return $this;
    }

    public function removeTag(\AdEntify\CoreBundle\Entity\Tag $tag)
    {
        $this->tags->removeElement($tag);
        $tag->setOwner(null);
    }

    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param Client $client
     * @return $this
     */
    public function addClient(\AdEntify\CoreBundle\Entity\OAuth\Client $client)
    {
        $this->clients[] = $client;
        $client->addUser($this);
        return $this;
    }

    /**
     * @param Client $client
     */
    public function removeClient(\AdEntify\CoreBundle\Entity\OAuth\Client $client)
    {
        $this->clients->removeElement($client);
        $client->removeUser($this);
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getClients()
    {
        return $this->clients;
    }

    /**
     * Check if client is in user client list
     *
     * @param Client $client
     * @return bool
     */
    public function isAuthorizedClient(Client $client)
    {
        $found = false;
        foreach($this->getClients() as $c) {
            if ($c->getId() == $client->getId()) {
                $found = true;
                break;
            }
        }

        return $found;
    }

    /**
     * @param mixed $facebookAccessToken
     */
    public function setFacebookAccessToken($facebookAccessToken)
    {
        $this->facebookAccessToken = $facebookAccessToken;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFacebookAccessToken()
    {
        return $this->facebookAccessToken;
    }
}