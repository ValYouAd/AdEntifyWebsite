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
use Buzz\Client\ClientInterface as HttpClientInterface;
use Buzz\Message\MessageInterface as HttpMessageInterface;
use Buzz\Message\Request as HttpRequest;
use Buzz\Message\Response as HttpResponse;

class TwitterGrantExtension implements GrantExtensionInterface
{
    protected $userManager = null;
    protected $ownerMap = null;
    protected $clientId = null;
    protected $clientSecret = null;
    protected $httpClient = null;

    public function __construct(UserManager $userManager, $ownerMap, HttpClientInterface $httpClient, $clientId, $clientSecret)
    {
        $this->userManager = $userManager;
        $this->ownerMap = $ownerMap;
        $this->httpClient = $httpClient;
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
        if (!isset($inputData['twitter_access_token_secret'])) {
            return false;
        }

        $signatureMethod = 'HMAC-SHA1';

        $parameters = array(
            'oauth_consumer_key'     => $this->clientId,
            'oauth_timestamp'        => time(),
            'oauth_nonce'            => $this->generateNonce(),
            'oauth_version'          => '1.0',
            'oauth_signature_method' => $signatureMethod,
            'oauth_token'            => $inputData['twitter_access_token']
        );

        $url = 'https://api.twitter.com/1.1/account/verify_credentials.json';
        $parameters['oauth_signature'] = OAuthUtils::signRequest(
            HttpRequestInterface::METHOD_GET,
            $url,
            $parameters,
            $this->clientSecret,
            $inputData['twitter_access_token_secret'],
            $signatureMethod
        );

        $userInformation = $this->httpRequest($url, null, $parameters)->getContent();
        if (empty($userInformation))
            return false;

        $userInformation = json_decode($userInformation);
        if (property_exists($userInformation, 'id')) {
            $user = $this->userManager->findUserBy(array(
                'twitterId' => $userInformation->id
            ));

            // If no user found, register a new user and grant token
            if (null === $user) {
                $user = $this->userManager->createUser();
                $user->setEnabled(true);
                $user->setPlainPassword(CommonTools::randomPassword()); // set random password to avoid login with just email

                $user->setTwitterAccessToken($inputData['twitter_access_token']);
                $user->setTwitterData($userInformation);
                $this->userManager->updateUser($user);
            }

            return array(
                'data' => $user
            );
        } else {
            return false;
        }
    }

    /**
     * Generate a non-guessable nonce value.
     *
     * @return string
     */
    protected function generateNonce()
    {
        return md5(microtime(true).uniqid('', true));
    }

    /**
     * {@inheritDoc}
     */
    protected function httpRequest($url, $content = null, $parameters = array(), $headers = array(), $method = null)
    {
        foreach ($parameters as $key => $value) {
            $parameters[$key] = $key . '="' . rawurlencode($value) . '"';
        }

        $headers[] = 'Authorization: OAuth ' . implode(', ', $parameters);

        if (null === $method) {
            $method = null === $content ? HttpRequestInterface::METHOD_GET : HttpRequestInterface::METHOD_POST;
        }

        $request  = new HttpRequest($method, $url);
        $response = new HttpResponse();

        $headers = array_merge(
            array(
                'User-Agent: HWIOAuthBundle (https://github.com/hwi/HWIOAuthBundle)',
            ),
            $headers
        );

        $request->setHeaders($headers);
        $request->setContent($content);

        $this->httpClient->send($request, $response);

        return $response;
    }
}