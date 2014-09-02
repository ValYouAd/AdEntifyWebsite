<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 04/12/2013
 * Time: 16:26
 */

namespace AdEntify\UserBundle\EventListener;

use AdEntify\CoreBundle\Services\EmailService;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\UserEvent;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Security\LoginManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RegistrationListener implements EventSubscriberInterface
{
    private $router;
    private $loginManager;
    private $firewallName;
    protected $emailer;

    public function __construct(UrlGeneratorInterface $router, LoginManagerInterface $loginManager, $firewallName, EmailService $emailer)
    {
        $this->router = $router;
        $this->loginManager = $loginManager;
        $this->firewallName = $firewallName;
        $this->emailer = $emailer;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistrationSuccess',
            FOSUserEvents::REGISTRATION_INITIALIZE => 'onRegistrationInit',
        );
    }

    public function onRegistrationSuccess(FormEvent $event)
    {
        $user = $event->getForm()->getData();
        $user->setEnabled(true);

        if ($user->isEnabled()) {
            $this->emailer->register($user);
        } else {
            $this->emailer->registerWithValidation($user);
        }

        $url = $this->router->generate('loggedInHome', array(
            'accountDisabled' => true
        ));

        $event->setResponse(new RedirectResponse($url));
    }

    /**
     * take action when registration is initialized
     * set the username to a unique id
     * @param \FOS\UserBundle\Event\FormEvent $event
     */
    public function onRegistrationInit(UserEvent $userevent)
    {
        $user = $userevent->getUser();
        $user->setUsername(uniqid());
    }
}