<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 15/04/2013
 * Time: 11:29
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Model;

/**
 * Class Mail
 *
 * @package AdEntify\CoreBundle\Model
 */
class Mail
{
    private $subject;
    private $from;
    private $to;

    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setTo($to)
    {
        $this->to = $to;

        return $this;
    }

    public function getTo()
    {
        return $this->to;
    }
}