<?php

namespace AdEntify\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

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

                    if (null === $user) {
                        $user = $userManager->createUser();
                        $user->setEnabled(true);
                        $user->setPlainPassword($this->randomPassword()); // set random password to avoid login with just email
                    }

                    $user->setFBData($fbdata);
                    $userManager->updateUser($user);

                    if (empty($user)) {
                        throw new UsernameNotFoundException('The user is not authenticated on facebook');
                    }

                    $token = new UsernamePasswordToken($user, $user->getPassword(), 'secured_area', $user->getRoles());

                    $this->container->get('security.context')->setToken($token);
                } catch(\FacebookApiExceptionion $e) {
                    return $this->redirect($this->generateUrl("root_url"));
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

    private function postUrl($url, $params) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, null, '&'));
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
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
