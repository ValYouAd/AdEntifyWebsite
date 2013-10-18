<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 24/04/2013
 * Time: 13:20
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\UserBundle\Security\User\Provider;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\HttpFoundation\Session;
use \TwitterOAuth;
use FOS\UserBundle\Entity\UserManager;
use Symfony\Component\Validator\Validator;

class TwitterProvider implements UserProviderInterface
{
    /**
     * @var \Twitter
     */
    protected $twitter_oauth;
    protected $userManager;
    protected $validator;
    protected $session;

    public function __construct(TwitterOAuth $twitter_oauth, $userManager, $validator, $session)
    {
        $this->twitter_oauth = $twitter_oauth;
        $this->userManager = $userManager;
        $this->validator = $validator;
        $this->session = $session;
    }

    public function supportsClass($class)
    {
        return $this->userManager->supportsClass($class);
    }

    public function findUserByTwitterId($twitterID)
    {
        return $this->userManager->findUserBy(array('twitterId' => $twitterID));
    }

    public function findUserByTwitterUserName($twitterUsername)
    {
        return $this->userManager->findUserBy(array('twitterUsername' => $twitterUsername));
    }

    public function loadUserByUsername($username)
    {
        $user = $this->findUserByTwitterUserName($username);

        $this->twitter_oauth->setOAuthToken($this->session->get('access_token'), $this->session->get('access_token_secret'));

        try {
            $info = $this->twitter_oauth->get('account/verify_credentials');
        } catch (Exception $e) {
            $info = null;
        }

        if (!empty($info)) {
            if (empty($user)) {
                $user = $this->userManager->createUser();
                $user->setEnabled(true);
                $user->setPlainPassword($this->randomPassword());
            }

            $email = $user->getEmail();
            if (empty($email))
                $user->setEmail('');
            $user->setTwitterId($info->id);
            $user->setUsername($info->name);
            $user->setTwitterUsername($info->name);
            $user->setFirstname($info->screen_name);
            $user->addRole('ROLE_TWITTER');
            if (count($this->validator->validate($user))) {
                throw new UsernameNotFoundException('The twitter user could not be stored');
            }

            $this->userManager->updateUser($user);
        }

        if (empty($user)) {
            throw new UsernameNotFoundException('The user is not authenticated on twitter');
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user)) || !$user->getTwitterId()) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getTwitterId());
    }

    /**
     * @return string
     *
     * Generate a random password of 12 caracters
     */
    private function randomPassword() {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789@!#&-_()?!";
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 15; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
    }
}