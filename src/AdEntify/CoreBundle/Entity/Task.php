<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 22/07/2013
 * Time: 14:14
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * Task
 *
 * @Serializer\XmlRoot("task")
 * @Serializer\ExclusionPolicy("none")
 *
 * @ORM\Table(name="tasks", indexes={@ORM\Index(name="search_idx", columns={"task_type"})})
 * @ORM\Entity
 */
class Task
{
    // TYPES
    const TYPE_UPLOAD = 'upload';
    const TYPE_UPLOAD_BRAND = 'upload-brand';
    // STATUS
    const STATUS_WAITING = 'waiting';
    const STATUS_INPROGRESS = 'inprogress';
    const STATUS_ERROR = 'error';

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
     * @ORM\Column(name="task_type", type="string", length=50)
     */
    private $type = self::TYPE_UPLOAD;

    /**
     * @var datetime
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(name="created_at", type="datetime")
     */
    private $createdAt;

    /**
     * @var datetime
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(name="launched_at", type="datetime")
     */
    private $launchedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=50)
     */
    private $status = self::STATUS_WAITING;

    /**
     * @ORM\ManyToOne(targetEntity="AdEntify\CoreBundle\Entity\User")
     */
    private $user;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text")
     */
    private $message;

    /**
     * @var bool
     *
     * @ORM\Column(name="notify_completed", type="boolean")
     */
    private $notifyCompleted = true;

    /**
     * @var
     *
     * @ORM\Column(name="error_message", type="text", nullable=true)
     */
    private $errorMessage;

    /**
     * @var int
     *
     * @ORM\Column(name="attempt", type="integer")
     */
    private $attempt = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="progress", type="smallint")
     */
    private $progress = 0;

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
     * @param \AdEntify\CoreBundle\Entity\datetime $launchedAt
     */
    public function setLaunchedAt($launchedAt)
    {
        $this->launchedAt = $launchedAt;
        return $this;
    }

    /**
     * @return \AdEntify\CoreBundle\Entity\datetime
     */
    public function getLaunchedAt()
    {
        return $this->launchedAt;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param boolean $notifyCompleted
     */
    public function setNotifyCompleted($notifyCompleted)
    {
        $this->notifyCompleted = $notifyCompleted;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getNotifyCompleted()
    {
        return $this->notifyCompleted;
    }

    /**
     * @param string $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param int $attempt
     */
    public function setAttempt($attempt)
    {
        $this->attempt = $attempt;
        return $this;
    }

    /**
     * @return int
     */
    public function getAttempt()
    {
        return $this->attempt;
    }

    /**
     * @param mixed $errorMessage
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param int $progress
     */
    public function setProgress($progress)
    {
        $this->progress = $progress;
        return $this;
    }

    /**
     * @return int
     */
    public function getProgress()
    {
        return $this->progress;
    }
}