<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 30/04/2013
 * Time: 16:30
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity\OAuth;

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
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @var
     */
    protected $name;

    public function __construct()
    {
        parent::__construct();
        // your own logic
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
}