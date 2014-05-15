<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 27/06/2013
 * Time: 14:32
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\UserBundle\Security\User\Provider;

use AdEntify\CoreBundle\Entity\User;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\Security\Core\User\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;

class FOSUBUserProvider extends BaseClass
{
    protected $container;

    public function __construct(UserManagerInterface $userManager, array $properties, $container)
    {
        $this->userManager = $userManager;
        $this->properties  = $properties;
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $property = $this->getProperty($response);
        $username = $response->getUsername();

        // on connect - get the access token and the user ID
        $service = $response->getResourceOwner()->getName();

        $setter = 'set'.ucfirst($service);
        $setter_id = $setter.'Id';
        $setter_token = $setter.'AccessToken';

        // we "disconnect" previously connected users
        if (null !== $previousUser = $this->userManager->findUserBy(array($property => $username))) {
            $previousUser->$setter_id(null);
            $previousUser->$setter_token(null);
            $this->userManager->updateUser($previousUser);
        }

        // we connect current user
        $user->$setter_id($username);
        $user->$setter_token($response->getAccessToken());

        $this->userManager->updateUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $user = $this->userManager->findUserBy(array($this->getProperty($response) => $response->getUsername()));

        //when the user is registrating
        if (null === $user) {
            $username = $response->getNickname();
            $service = $response->getResourceOwner()->getName();
            $setter = 'set'.ucfirst($service);
            $setter_id = $setter.'Id';
            $setter_token = $setter.'AccessToken';
            // create new user here
            $user = $this->userManager->createUser();
            $user->$setter_id($response->getUsername());
            $user->$setter_token($response->getAccessToken());
            if ($response->getRealName()) {
                $parts = explode(' ', $response->getRealName());
                $user->setFirstname(array_shift($parts));
                $user->setLastname(implode(' ', $parts));
            }
            //I have set all requested data with the user's username
            //modify here with relevant data
            $resp = $response->getResponse();
            if (array_key_exists('profile_image_url', $resp))
                $user->setProfilePicture($resp['profile_image_url']);
            if (array_key_exists('first_name', $resp))
                $user->setFirstname($resp['first_name']);
            if (array_key_exists('last_name', $resp))
                $user->setLastname($resp['last_name']);
            if (array_key_exists('gender', $resp))
                $user->setGender($resp['gender']);
            if (array_key_exists('email', $resp))
                $user->setEmail($resp['email']);
            else
                $user->setEmail($response->getUsername());
            $user->setUsername($username);
            $user->setPlainPassword($this->randomPassword());
            $user->setEnabled(true);

            if ($user->isEnabled()) {
                $this->container->get('ad_entify_core.email')->register($user);
            } else {
                $this->container->get('ad_entify_core.email')->registerWithValidation($user);
            }

            $this->userManager->updateUser($user);
            return $user;
        }

        //if user exists - go with the HWIOAuth way
        $user = parent::loadUserByOAuthUserResponse($response);

        $serviceName = $response->getResourceOwner()->getName();
        $setter = 'set' . ucfirst($serviceName) . 'AccessToken';

        //update access token
        $user->$setter($response->getAccessToken());

        $user->setLoggedInCount($user->getLoggedInCount() + 1);

        return $user;
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