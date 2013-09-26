<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 30/04/2013
 * Time: 16:30
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity\OAuth;

use AdEntify\CoreBundle\Entity\User;
use FOS\OAuthServerBundle\Entity\Client as BaseClient;
use Doctrine\ORM\Mapping as ORM;

/**
 * Client
 *
 * @ORM\Table(name="clients", indexes={@ORM\Index(name="search_idx", columns={"name"})})
 * @ORM\Entity
 */
class Client extends BaseClient
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     *
     * @var
     */
    protected $name;

    /**
     * @ORM\ManyToMany(targetEntity="AdEntify\CoreBundle\Entity\User", mappedBy="clients")
     */
    protected $users;

    public function __construct()
    {
        parent::__construct();

        $this->users = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $users
     */
    public function addUser(User $user)
    {
        $this->users[] = $user;
        return $this;
    }

    public function removeUser(User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }
}