<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 17/07/2013
 * Time: 12:35
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity;

use AdEntify\CoreBundle\Util\CommonTools;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation as Serializer;

/**
 * ShortUrl
 *
 * @Serializer\XmlRoot("shortUrl")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="short_urls")
 * @ORM\Entity(repositoryClass="AdEntify\CoreBundle\Entity\ShortUrlRepository")
 */
class ShortUrl
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
     * @var string
     *
     * @ORM\Column(name="url", type="text")
     */
    private $url;

    /**
     * @var integer
     *
     * @ORM\Column(name="visit_count", type="bigint")
     */
    private $visitCount = 0;

    /**
     * @var datetime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $created_at;

    /**
     * @var datetime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="last_visited", type="datetime", nullable=true)
     */
    private $last_visited;

    /**
     * @param \AdEntify\CoreBundle\Entity\datetime $created_at
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * @return \AdEntify\CoreBundle\Entity\datetime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \AdEntify\CoreBundle\Entity\datetime $last_visited
     */
    public function setLastVisited($last_visited)
    {
        $this->last_visited = $last_visited;
        return $this;
    }

    /**
     * @return \AdEntify\CoreBundle\Entity\datetime
     */
    public function getLastVisited()
    {
        return $this->last_visited;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param int $visitCount
     */
    public function setVisitCount($visitCount)
    {
        $this->visitCount = $visitCount;
        return $this;
    }

    /**
     * @return int
     */
    public function getVisitCount()
    {
        return $this->visitCount;
    }

    public function getBase62Id()
    {
        return CommonTools::base_to($this->getId(), 10, 62);
    }
}