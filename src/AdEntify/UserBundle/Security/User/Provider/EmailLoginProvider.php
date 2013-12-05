<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 08/04/2013
 * Time: 19:46
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\UserBundle\Security\User\Provider;

use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class EmailLoginProvider implements UserProviderInterface
{
    private $userManager;

    public function __construct(UserProviderInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    public function loadUserByUsername($username)
    {
        $user = $this->userManager->findUserByEmail($username);

        if (!$user) {
            throw new UsernameNotFoundException(sprintf('Vous n\'êtes pas inscrit(e) avec l\'email "%s"', $username));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        return $this->userManager->refreshUser($user);
    }

    public function supportsClass($class)
    {
        return $this->userManager->supportsClass($class);
    }
}
