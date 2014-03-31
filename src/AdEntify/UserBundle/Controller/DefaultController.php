<?php

namespace AdEntify\UserBundle\Controller;

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

class DefaultController extends Controller
{
    /**
     * @Route("/facebook/login-check", name="_security_check_facebook")
     */
    public function facebookLoginCheckAction(Request $request)
    {
        if ($request->query->has('error'))
            return $this->redirect($this->generateUrl("root_url"));
        else {
            $url = 'https://graph.facebook.com/oauth/access_token';
            $token_params = array(
                "code" => $request->query->get('code'),
                "client_id" => $this->container->getParameter('facebook_app_id'),
                "client_secret" => $this->container->getParameter('facebook_secret'),
                "redirect_uri" => $this->generateUrl('_security_check_facebook', array(), true)
            );
            parse_str($this->postUrl($url, $token_params), $response);
            if (array_key_exists('access_token', $response) && array_key_exists('expires', $response)) {
                $response['access_token'];

                $fb = $this->container->get('fos_facebook.api');
                $fb->setAccessToken($response['access_token']);
                try {
                    $fbdata = $fb->api('/me');

                    $userManager = $this->container->get('fos_user.user_manager');
                    $user = $userManager->findUserBy(array('facebookId' => $fbdata['id']));

                    $newUser = false;
                    if (null === $user) {
                        $user = $userManager->createUser();
                        $user->setEnabled(false);
                        $user->setPlainPassword(CommonTools::randomPassword()); // set random password to avoid login with just email

                        $newUser = true;
                    }

                    $user->setLoggedInCount($user->getLoggedInCount() + 1);
                    $user->setFacebookAccessToken($fb->getAccessToken());
                    $user->setFBData($fbdata);
                    $userManager->updateUser($user);

                    if ($newUser) {
                        if ($user->isEnabled()) {
                            $this->get('ad_entify_core.email')->register($user);
                        } else {
                            $this->get('ad_entify_core.email')->registerWithValidation($user);
                        }
                    }

                    if (empty($user)) {
                        throw new UsernameNotFoundException('The user is not authenticated on facebook');
                    }

                    if ($user->isEnabled()) {
                        $token = new UsernamePasswordToken($user, $user->getPassword(), 'secured_area', $user->getRoles());
                        $this->container->get('security.context')->setToken($token);
                    }
                } catch(\FacebookApiException $e) {
                    return $this->redirect($this->generateUrl("root_url"));
                }

                if ($user->isEnabled()) {
                    if ($request->getSession()->has('_security.main.target_path')) {
                        return $this->redirect($request->getSession()->get('_security.main.target_path'));
                    } else {
                        return $this->redirect($this->generateUrl("root_url"));
                    }
                } else {
                    return $this->redirect($this->generateUrl('loggedInHome', array(
                        'accountDisabled' => true
                    )));
                }
            }

            return $this->redirect($this->generateUrl("root_url"));
        }
    }

    /**
     * @Route("/facebook/logout-check", name="_security_logout_facebook")
     */
    public function facebookLogoutAction()
    {

    }

    /**
     * @Route("/token/facebook")
     */
    public function facebookAccessTokenAction()
    {
        if (!$this->getRequest()->request->has('access_token')) {
            throw new HttpException(403);
        }

        // Get AdEntify OAuth client
        $oAuthClient = $this->getOAuthClient();

        // Get OAuth token with facebook grant type
        $url = $this->generateUrl('fos_oauth_server_token', array(), true);
        $params = array(
            "client_id" => $oAuthClient->getPublicId(),
            "client_secret" => $oAuthClient->getSecret(),
            "grant_type" => $this->container->getParameter('facebook_grant_extension_uri'),
            "facebook_access_token" => $this->getRequest()->request->get('access_token')
        );
        $result = $this->postUrl($url, $params);
        $tokens = !empty($result) ? json_decode($result) : null;

        // If no error, return the tokens
        // Else, throw an error
        if (null !== $tokens && !isset($tokens->error)) {
            $response = new Response($result);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else {
            throw new HttpException(403);
        }
    }

    /**
     * @Route("/token/twitter")
     */
    public function twitterAccessTokenAction()
    {
        if (!$this->getRequest()->request->has('access_token')) {
            throw new HttpException(403);
        }

        // Get AdEntify OAuth client
        $oAuthClient = $this->getOAuthClient();

        // Get OAuth token with facebook grant type
        $url = $this->generateUrl('fos_oauth_server_token', array(), true);
        $params = array(
            "client_id" => $oAuthClient->getPublicId(),
            "client_secret" => $oAuthClient->getSecret(),
            "grant_type" => $this->container->getParameter('facebook_grant_extension_uri'),
            "facebook_access_token" => $this->getRequest()->request->get('access_token')
        );
        $result = $this->postUrl($url, $params);
        $tokens = !empty($result) ? json_decode($result) : null;

        // If no error, return the tokens
        // Else, throw an error
        if (null !== $tokens && !isset($tokens->error)) {
            $response = new Response($result);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else {
            throw new HttpException(403);
        }
    }

    /**
     * @Route("/token/password")
     */
    public function passswordAccessTokenAction()
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
        $result = $this->postUrl($url, $params);
        $tokens = !empty($result) ? json_decode($result) : null;

        // If no error, return the tokens
        // Else, throw an error
        if (null !== $tokens && !isset($tokens->error)) {
            $response = new Response($result);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else {
            throw new HttpException(403);
        }
    }

    /**
     * @Route("/token/user-logged-access-token", name="get_user_access_token")
     * @Method({"POST"})
     */
    public function getUserAccessTokenAction()
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $user = $this->container->get('security.context')->getToken()->getUser();

            $token = $this->getDoctrine()->getManager()
                ->createQuery('SELECT token FROM AdEntify\CoreBundle\Entity\OAuth\AccessToken token
                WHERE token.user = :user AND token.expiresAt > :currentTimestamp')
                ->setMaxResults(1)
                ->setParameters(array(
                    'user' => $user->getId(),
                    'currentTimestamp' => time()
                ))->getOneOrNullResult();

            $accessToken = null;
            // Get existing token
            if ($token) {
                $accessToken = array(
                    'access_token' => $token->getToken(),
                    'expires_at' => $token->getExpiresAt(),
                    'user_id' => $token->getUser()->getId()
                );
            } else {
                // Delete old tokens
                $this->getDoctrine()->getManager()
                    ->createQuery('DELETE FROM AdEntify\CoreBundle\Entity\OAuth\AccessToken token
                  WHERE token.user = :user')
                    ->setParameters(array(
                        'user' => $user->getId()
                    ))->execute();
                $this->getDoctrine()->getManager()
                    ->createQuery('DELETE FROM AdEntify\CoreBundle\Entity\OAuth\RefreshToken token
                  WHERE token.user = :user')
                    ->setParameters(array(
                        'user' => $user->getId()
                    ))->execute();

                // Get client
                $client = $this->getOAuthClient();

                if (true === $this->container->get('session')->get('_fos_oauth_server.ensure_logout')) {
                    $this->container->get('security.context')->setToken(null);
                    $this->container->get('session')->invalidate();
                }

                // Get access token
                try {
                    $accessToken = $this->container
                        ->get('fos_oauth_server.server')
                        ->createAccessToken($client, $user);
                } catch (OAuth2ServerException $e) {
                    return $e->getHttpResponse();
                }
            }

            if (is_array($accessToken))
                $accessToken['user_id'] = $user->getId();

            $response = new Response(json_encode($accessToken));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else {
            $response = new Response(json_encode(array(
                'access_token' => 'guest',
                'expires_at' => 0,
                'user_id' => 0
            )));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }

    private function getOAuthClient()
    {
        return $this->container->get('fos_oauth_server.client_manager.default')->findClientBy(array(
            'name' => $this->container->getParameter('adentify_oauth_client_name')
        ));
    }

    private function postUrl($url, $params) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, null, '&'));
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }
}
