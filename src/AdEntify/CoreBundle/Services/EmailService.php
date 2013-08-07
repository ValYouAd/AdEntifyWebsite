<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 15/04/2013
 * Time: 11:25
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Services;

use \Symfony\Bundle\TwigBundle\TwigEngine;

class EmailService
{
    protected $template;
    protected $mailer;
    protected $contactEmail;
    protected $fromEmail;

    /**
     * @param TwigEngine $template
     * @param \Swift_Mailer $mailer
     * @param $contactEmail
     * @param $fromEmail
     */
    public function __construct(TwigEngine $template, \Swift_Mailer $mailer, $contactEmail, $fromEmail) {
        $this->template = $template;
        $this->mailer = $mailer;
        $this->contactEmail = $contactEmail;
        $this->fromEmail = $fromEmail;
    }


}