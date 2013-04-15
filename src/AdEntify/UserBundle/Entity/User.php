<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 08/04/2013
 * Time: 19:42
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\UserBundle\Entity;

use FOS\UserBundle\Entity\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User extends BaseUser
{
    const GENDER_MALE = 0;
    const GENDER_FEMALE = 1;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="lastname", type="string", length=255)
     */
    private $lastname;

    /**
     * @var datetime $created_on
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_on", type="datetime")
     */
    private $created_on;

    /**
     * @var integer
     *
     * @ORM\Column(name="facebook_id", type="bigint")
     */
    protected $facebookId;

    /**
     * @ORM\Column(type="smallint", nullable=false)
     */
    private $gender = self::GENDER_FEMALE;

    public function __construct()
    {
        parent::__construct();

        if ($this->getCreatedOn() === null)
            $this->setCreatedOn(new \DateTime('now'));
    }

    /**
     * @param \AdEntify\UserBundle\Entity\datetime $created_on
     */
    public function setCreatedOn($created_on)
    {
        $this->created_on = $created_on;
    }

    /**
     * @return \AdEntify\UserBundle\Entity\datetime
     */
    public function getCreatedOn()
    {
        return $this->created_on;
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
            $this->salt = '';
        }
    }

    /**
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebookId;
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
        if (isset($fbdata['gender'])) {
            if ($fbdata['gender'] == 'male')
                $this->setGender(self::GENDER_MALE);
            if ($fbdata['gender'] == 'female')
                $this->setGender(self::GENDER_FEMALE);
        }
    }
}