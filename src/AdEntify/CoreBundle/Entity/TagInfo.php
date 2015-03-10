<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 23/02/15
 * Time: 15:58
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * Tag
 *
 * @Serializer\XmlRoot("tagInfo")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="tag_infos")
 * @ORM\Entity(repositoryClass="AdEntify\CoreBundle\Entity\TagInfoRepository")
 */
class TagInfo
{
    const TYPE_ADVERTISING = 'advertising';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"details", "list"})
     */
    protected $id;

    /**
     * @var Tag
     *
     * @ORM\OneToOne(targetEntity="AdEntify\CoreBundle\Entity\Tag", mappedBy="tagInfo")
     * @Serializer\Groups({"details", "list"})
     */
    protected $tag;

    /**
     * @var
     *
     * @ORM\Column(name="info", type="array")
     * @Serializer\Groups({"details", "list"})
     */
    protected $info;

    /**
     * @var
     *
     * @ORM\Column(name="type", type="string", length=50)
     * @Serializer\Groups({"details", "list"})
     */
    protected $type = self::TYPE_ADVERTISING;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getInfo()
    {
        return $this->info;
    }

    /**
     * @param mixed $info
     */
    public function setInfo($info)
    {
        $this->info = $info;
        return $this;
    }

    /**
     * @return Tag
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param Tag $tag
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}