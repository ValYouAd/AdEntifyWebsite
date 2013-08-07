<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 23/04/2013
 * Time: 09:43
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * OAuthUserInfo
 *
 * @Serializer\XmlRoot("oauthuserinfo")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="oauth_user_infos", indexes={@ORM\Index(name="search_idx", columns={"service_name","service_user_id"})})
 * @ORM\Entity
 */
class OAuthUserInfo
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\User", inversedBy="oAuthUserInfos")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="service_name", type="string", length=255)
     */
    private $serviceName;

    /**
     * @var string
     *
     * @ORM\Column(name="service_user_id", type="string", length=255)
     */
    private $serviceUserId;

    /**
     * @var string
     *
     * @ORM\Column(name="service_username", type="string", length=255)
     */
    private $serviceUsername;

    /**
     * @var string
     *
     * @ORM\Column(name="service_full_name", type="string", length=255, nullable=true)
     */
    private $serviceFullName;

    /**
     * @var text
     *
     * @ORM\Column(name="service_access_token", type="text")
     */
    private $serviceAccessToken;

    /**
     * @var text
     *
     * @ORM\Column(name="service_access_token_secret", type="text", nullable=true)
     */
    private $serviceAccessTokenSecret;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \AdEntify\CoreBundle\Entity\text $serviceAccessToken
     */
    public function setServiceAccessToken($serviceAccessToken)
    {
        $this->serviceAccessToken = $serviceAccessToken;
        return $this;
    }

    /**
     * @return \AdEntify\CoreBundle\Entity\text
     */
    public function getServiceAccessToken()
    {
        return $this->serviceAccessToken;
    }

    /**
     * @param string $serviceFullName
     */
    public function setServiceFullName($serviceFullName)
    {
        $this->serviceFullName = $serviceFullName;
        return $this;
    }

    /**
     * @return string
     */
    public function getServiceFullName()
    {
        return $this->serviceFullName;
    }

    /**
     * @param string $serviceUserId
     */
    public function setServiceUserId($serviceUserId)
    {
        $this->serviceUserId = $serviceUserId;
        return $this;
    }

    /**
     * @return string
     */
    public function getServiceUserId()
    {
        return $this->serviceUserId;
    }

    /**
     * @param string $serviceName
     */
    public function setServiceName($serviceName)
    {
        $this->serviceName = $serviceName;
        return $this;
    }

    /**
     * @return string
     */
    public function getServiceName()
    {
        return $this->serviceName;
    }

    /**
     * @param string $serviceUsername
     */
    public function setServiceUsername($serviceUsername)
    {
        $this->serviceUsername = $serviceUsername;
        return $this;
    }

    /**
     * @return string
     */
    public function getServiceUsername()
    {
        return $this->serviceUsername;
    }

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \AdEntify\CoreBundle\Entity\text $serviceAccessTokenSecret
     */
    public function setServiceAccessTokenSecret($serviceAccessTokenSecret)
    {
        $this->serviceAccessTokenSecret = $serviceAccessTokenSecret;
        return $this;
    }

    /**
     * @return \AdEntify\CoreBundle\Entity\text
     */
    public function getServiceAccessTokenSecret()
    {
        return $this->serviceAccessTokenSecret;
    }
}