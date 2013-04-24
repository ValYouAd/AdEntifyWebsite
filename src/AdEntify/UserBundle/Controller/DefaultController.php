<?php

namespace AdEntify\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DefaultController extends Controller
{
    /**
     * @Route("/facebook/login-check", name="_security_check_facebook")
     */
    public function facebookLoginCheckAction()
    {

    }

    /**
     * @Route("/facebook/login-check", name="_security_logout_facebook")
     */
    public function facebookLogoutAction()
    {

    }

    /**
     * @Route("/twitter/login-check", name="_security_check_twitter")
     */
    public function twitterLoginCheckAction()
    {
        echo 'lal';
        die;
    }

    /**
     * @Route("/twitter/login", name="_security_login_twitter")
     */
    public function twitterLoginAction()
    {
        $request = $this->get('request');
        $twitter = $this->get('fos_twitter.service');

        $authURL = $twitter->getLoginUrl($request);

        $response = new RedirectResponse($authURL);

        return $response;
    }
}
