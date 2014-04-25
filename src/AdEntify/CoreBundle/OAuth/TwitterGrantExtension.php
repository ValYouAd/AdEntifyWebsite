<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 25/04/2014
 * Time: 15:28
 */

namespace AdEntify\CoreBundle\OAuth;

use AdEntify\CoreBundle\Util\CommonTools;
use FOS\OAuthServerBundle\Storage\GrantExtensionInterface;
use FOS\UserBundle\Doctrine\UserManager;
use OAuth2\Model\IOAuth2Client;

class TwitterGrantExtension implements GrantExtensionInterface
{
    protected $userManager = null;
    protected $facebookSdk = null;

    public function __construct(UserManager $userManager, \BaseFacebook $facebookSdk)
    {
        $this->userManager = $userManager;
        $this->facebookSdk = $facebookSdk;
    }

    /**
     * @see OAuth2\IOAuth2GrantExtension::checkGrantExtension
     */
    public function checkGrantExtension(IOAuth2Client $client, array $inputData, array $authHeaders)
    {
        if (!isset($inputData['twitter_access_token'])) {
            return false;
        }

        $this->facebookSdk->setAccessToken($inputData['twitter_access_token']);
        try {
            // Try to get the user with the facebook token from Open Graph
            $fbData = $this->facebookSdk->api('/me');

            if (empty($fbData) || !isset($fbData['id'])) {
                return false;
            }

            // Check if a user match in AdEntify database with the facebook id
            $user = $this->userManager->findUserBy(array(
                'facebookId' => $fbData['id'],
            ));

            // If no user found, register a new user and grant token
            if (null === $user) {
                $user = $this->userManager->createUser();
                $user->setEnabled(true);
                $user->setPlainPassword(CommonTools::randomPassword()); // set random password to avoid login with just email

                $user->setFacebookAccessToken($this->facebookSdk->getAccessToken());
                $user->setFBData($fbData);
                $this->userManager->updateUser($user);
            }

            return array(
                'data' => $user
            );
        } catch(\Exception $e) {
            return false;
        }
    }
}