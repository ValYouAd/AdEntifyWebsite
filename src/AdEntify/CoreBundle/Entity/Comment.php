<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 16/04/2013
 * Time: 12:10
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Comment
 *
 * @Serializer\XmlRoot("comment")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="comments")
 * @ORM\Entity
 */
class Comment
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="body", type="text")
     * @Assert\NotBlank(message="comment.body.notblank")
     *
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $body;

    /**
     * @var datetime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     * @Assert\DateTime()
     *
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $createdAt;

    /**
     * @var datetime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     * @Assert\DateTime()
     */
    private $deletedAt;

    /**
     * @Serializer\Exclude
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\Photo", inversedBy="comments")
     */
    private $photo;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\User", inversedBy="comments")
     *
     * @Serializer\Groups({"details", "list", "slight-list"})
     */
    private $author;

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
     * @param string $body
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param \AdEntify\CoreBundle\Entity\datetime $createdAt
     * @return Comment
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
     * @param \AdEntify\CoreBundle\Entity\datetime $deletedAt
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    /**
     * @return \AdEntify\CoreBundle\Entity\datetime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    public function setPhoto($photo)
    {
        $this->photo = $photo;
        return $this;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setAuthor($author)
    {
        $this->author = $author;
        return $this;
    }

    public function getAuthor()
    {
        return $this->author;
    }
}