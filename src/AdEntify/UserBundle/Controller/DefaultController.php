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
            parse_str(CommonTools::postUrl($url, $token_params), $response);
            if (array_key_exists('access_token', $response) && array_key_exists('expires', $response)) {
                $fb = $this->container->get('fos_facebook.api');
                $fb->setAccessToken($response['access_token']);
                try {
                    $fbdata = $fb->api('/me');

                    $userManager = $this->container->get('fos_user.user_manager');
                    $user = $userManager->findUserBy(array('facebookId' => $fbdata['id']));
                    if (array_key_exists('email', $fbdata) && !empty($fbdata['email'])) {
                        $user = $userManager->findUserBy(array('email' => $fbdata['email']));
                    }

                    $newUser = false;
                    if (null === $user) {
                        // Check if there is a logged in user
                        $securityContext = $this->container->get('security.context');
                        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
                            $user = $this->container->get('security.context')->getToken()->getUser();
                        } else {
                            $user = $userManager->createUser();
                            $user->setEnabled(true);
                            $user->setPlainPassword(CommonTools::randomPassword()); // set random password to avoid login with just email

                            $newUser = true;
                        }
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

                $this->getDoctrine()->getRepository('AdEntifyCoreBundle:Person')->createFromUser($user);

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
     * @Route("/token/user-logged-access-token", name="get_user_access_token")
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
}
