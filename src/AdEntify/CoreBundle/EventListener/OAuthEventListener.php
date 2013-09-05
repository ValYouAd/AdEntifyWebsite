<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 05/09/2013
 * Time: 10:28
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\EventListener;

use FOS\OAuthServerBundle\Event\OAuthEvent;
use FOS\UserBundle\Doctrine\UserManager;


class OAuthEventListener
{
    protected $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function onPreAuthorizationProcess(OAuthEvent $event)
    {
        if ($user = $this->getUser($event)) {
            $event->setAuthorizedClient(
                $user->isAuthorizedClient($event->getClient())
            );
        }
    }

    public function onPostAuthorizationProcess(OAuthEvent $event)
    {
        if ($event->isAuthorizedClient()) {
            if (null !== $client = $event->getClient()) {
                $user = $this->getUser($event);
                $user->addClient($client);
                $this->userManager->updateUser($user);
            }
        }
    }

    protected function getUser(OAuthEvent $event)
    {
        return $this->userManager
            ->findUserByUsername($event->getUser()->getUsername());
    }
}