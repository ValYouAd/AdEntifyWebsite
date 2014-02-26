<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 28/08/2013
 * Time: 18:29
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Photo
 *
 * @Serializer\XmlRoot("local-upload")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="local_uploads")
 * @ORM\Entity
 */
class LocalUpload
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="bigint")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="AdEntify\CoreBundle\Entity\User")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $owner;

    /**
     * @var string
     *
     * @ORM\Column(name="uploaded_photos", type="text", nullable=true)
     */
    private $uploadedPhotos;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $owner
     */
    public function setOwner($owner)
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
     * @param string $uploadedPhotos
     */
    public function setUploadedPhotos($uploadedPhotos)
    {
        $this->uploadedPhotos = $uploadedPhotos;
        return $this;
    }

    /**
     * @return string
     */
    public function getUploadedPhotos()
    {
        return $this->uploadedPhotos;
    }
}