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
use HWI\Bundle\OAuthBundle\Security\OAuthUtils;
use OAuth2\Model\IOAuth2Client;
use Buzz\Message\RequestInterface as HttpRequestInterface;

class TwitterGrantExtension implements GrantExtensionInterface
{
    protected $userManager = null;
    protected $ownerMap = null;
    protected $clientId = null;
    protected $clientSecret = null;

    public function __construct(UserManager $userManager, $ownerMap, $clientId, $clientSecret)
    {
        $this->userManager = $userManager;
        $this->ownerMap = $ownerMap;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
    }

    /**
     * @see OAuth2\IOAuth2GrantExtension::checkGrantExtension
     */
    public function checkGrantExtension(IOAuth2Client $client, array $inputData, array $authHeaders)
    {
        if (!isset($inputData['twitter_access_token'])) {
            return false;
        }

        $resourceOwner = $this->ownerMap->getResourceOwnerByName('twitter');

        $parameters = array_merge(array(
            'oauth_consumer_key'     => $this->clientId,
            'oauth_timestamp'        => time(),
            'oauth_nonce'            => $this->generateNonce(),
            'oauth_version'          => '1.0',
            'oauth_signature_method' => $this->options['signature_method'],
            'oauth_token'            => $requestToken['oauth_token'],
            'oauth_verifier'         => $request->query->get('oauth_verifier'),
        ), $extraParameters);

        $url = $this->options['access_token_url'];
        $parameters['oauth_signature'] = OAuthUtils::signRequest(
            HttpRequestInterface::METHOD_POST,
            $url,
            $parameters,
            $this->clientSecret,
            $requestToken['oauth_token_secret'],
            $this->options['signature_method']
        );


        $userInformation = $resourceOwner->getUserInformation($accessToken);


        $this->facebookSdk->setAccessToken($inputData['twitter_access_token']);
        try {
            // Try to get the user with the facebook token from Open Graph
            $fbData = $this->facebookSdk->api('/me');

            if (empty($fbData) || !isset($fbData['id'])) {
                return false;
            }

            // Check if a user match in AdEntify database with the facebook id
            $user = $this->userManager->findUserBy(array(
                'twitterId' => $fbData['id'],
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