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
     * @Route("/facebook/logout-check", name="_security_logout_facebook")
     */
    public function facebookLogoutAction()
    {

    }
}
