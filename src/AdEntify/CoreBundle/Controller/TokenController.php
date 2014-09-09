<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 22/04/2014
 * Time: 14:53
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Util\CommonTools;
use OAuth2\OAuth2ServerException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\SecurityContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use JMS\SecurityExtraBundle\Annotation\Secure;
use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

/**
 * Class TokenController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("token")
 *
 */
class TokenController extends FosRestController
{
    /**
     * @View(serializerGroups={"details"})
     */
    public function postFacebookAction()
    {
        if (!$this->getRequest()->request->has('facebook_access_token')) {
            throw new HttpException(403);
        }

        // Get AdEntify OAuth client
        $oAuthClient = $this->getOAuthClient();

        // Get OAuth token with facebook grant type
        $url = $this->generateUrl('fos_oauth_server_token', array(), true);
        $params = array(
            'client_id' => $oAuthClient->getPublicId(),
            'client_secret' => $oAuthClient->getSecret(),
            'grant_type' => $this->container->getParameter('facebook_grant_extension_uri'),
            'facebook_access_token' => $this->getRequest()->request->get('facebook_access_token')
        );
        $result = CommonTools::postUrl($url, $params);

        $tokens = !empty($result) ? json_decode($result) : null;

        // If no error, return the tokens
        // Else, throw an error
        if (null !== $tokens && !isset($tokens->error)) {
            return (array)$tokens;
        } else {
            throw new HttpException(403, $tokens->error);
        }
    }

    /**
     * @View(serializerGroups={"details"})
     */
    public function postTwitterAction()
    {
        if (!$this->getRequest()->request->has('twitter_access_token')
            || !$this->getRequest()->request->has('twitter_access_token_secret')) {
            throw new HttpException(403);
        }

        // Get AdEntify OAuth client
        $oAuthClient = $this->getOAuthClient();

        // Get OAuth token with twitter grant type
        $url = $this->generateUrl('fos_oauth_server_token', array(), true);
        $params = array(
            "client_id" => $oAuthClient->getPublicId(),
            "client_secret" => $oAuthClient->getSecret(),
            "grant_type" => $this->container->getParameter('twitter_grant_extension_uri'),
            "twitter_access_token" => $this->getRequest()->request->get('twitter_access_token'),
            "twitter_access_token_secret" => $this->getRequest()->request->get('twitter_access_token_secret')
        );
        $result = CommonTools::postUrl($url, $params);
        $tokens = !empty($result) ? json_decode($result) : null;

        // If no error, return the tokens
        // Else, throw an error
        if (null !== $tokens && !isset($tokens->error)) {
            return (array)$tokens;
        } else {
            throw new HttpException(403, $tokens->error);
        }
    }

    /**
     * @View(serializerGroups={"details"})
     */
    public function postPasswordAction()
    {
        if (!$this->getRequest()->request->has('username') || !$this->getRequest()->request->has('password')) {
            throw new HttpException(403);
        }

        // Get AdEntify OAuth client
        $oAuthClient = $this->getOAuthClient();

        // Get OAuth token with password grant type
        $url = $this->generateUrl('fos_oauth_server_token', array(), true);
        $params = array(
            "client_id" => $oAuthClient->getPublicId(),
            "client_secret" => $oAuthClient->getSecret(),
            "grant_type" => 'password',
            "username" => $this->getRequest()->request->get('username'),
            "password" => $this->getRequest()->request->get('password'),
        );
        $result = CommonTools::postUrl($url, $params);
        $tokens = !empty($result) ? json_decode($result) : null;

        // If no error, return the tokens
        // Else, throw an error
        if (null !== $tokens && !isset($tokens->error)) {
            return (array)$tokens;
        } else {
            throw new HttpException(403, $tokens->error);
        }
    }

    private function getOAuthClient()
    {
        return $this->container->get('fos_oauth_server.client_manager.default')->findClientBy(array(
            'name' => $this->container->getParameter('adentify_oauth_client_name')
        ));
    }
} 