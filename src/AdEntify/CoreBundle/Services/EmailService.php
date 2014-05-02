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
use Symfony\Component\Translation\Translator;

class EmailService
{
    protected $template;
    protected $mailer;
    protected $contactEmail;
    protected $fromEmail;
    protected $translator;
    protected $teamEmail;

    /**
     * @param TwigEngine $template
     * @param \Swift_Mailer $mailer
     * @param $contactEmail
     * @param $fromEmail
     */
    public function __construct(TwigEngine $template, \Swift_Mailer $mailer, $contactEmail, $fromEmail, Translator $translator, $teamEmail) {
        $this->template = $template;
        $this->mailer = $mailer;
        $this->contactEmail = $contactEmail;
        $this->fromEmail = $fromEmail;
        $this->translator = $translator;
        $this->teamEmail = $teamEmail;
    }

    public function newUserRegistred($user) {
        $template = $this->template->render('AdEntifyCoreBundle:Email:new_user_registered.html.twig', array (
            'user' => $user->getFullname()
        ));

        return $this->sendEmail($this->translator->trans('email.new_user.title', array('user', $user->getFullname())), $template, $this->teamEmail);
    }

    /**
     * @param $user
     * @return bool|int
     */
    public function registerWithValidation($user) {
        $this->newUserRegistred($user);

        $template = $this->template->render('AdEntifyCoreBundle:Email:register_pending_validation.html.twig', array (
            'user' => $user->getFullname()
        ));

        if (filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            return $this->sendEmail($this->translator->trans('email.register_validation.title'), $template, $user->getEmail());
        } else
            return true;
    }

    /**
     * @param $user
     * @return bool|int
     */
    public function register($user) {
        $this->newUserRegistred($user);

        $template = $this->template->render('AdEntifyCoreBundle:Email:register.html.twig', array (
            'user' => $user->getFullname()
        ));

        if (filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            return $this->sendEmail($this->translator->trans('email.register.title'), $template, $user->getEmail());
        } else
            return true;
    }

    /**
     * Validate user account
     *
     * @param $user
     * @return bool|int
     */
    public function validateAccount($user)
    {
        $template = $this->template->render('AdEntifyCoreBundle:Email:validate_account.html.twig', array (
            'user' => $user->getFullname()
        ));

        if (filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)) {
            return $this->sendEmail($this->translator->trans('email.validate_account.title'), $template, $user->getEmail());
        } else
            return true;
    }

    /**
     * Send email
     *
     * @param $subject
     * @param $content
     * @param $to
     * @param bool $html
     * @return bool|int
     */
    public function sendEmail($subject, $content, $to, $html = true) {
        if (!$to)
            return false;

        $message = \Swift_Message::newInstance($subject)
            ->setFrom($this->fromEmail)
            ->setTo($to)
            ->setBody($content)
            ->setContentType($html ? 'text/html': 'text/plain');

        return $this->mailer->send($message);
    }
}