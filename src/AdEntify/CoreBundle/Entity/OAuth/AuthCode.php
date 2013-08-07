<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 30/04/2013
 * Time: 16:31
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity\OAuth;

use FOS\OAuthServerBundle\Entity\AuthCode as BaseAuthCode;
use Doctrine\ORM\Mapping as ORM;

/**
 * AuthCode
 *
 * @ORM\Table(name="authcodes")
 * @ORM\Entity
 */
class AuthCode extends BaseAuthCode
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Client")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $client;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\User")
     */
    protected $user;

}