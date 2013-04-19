<?php

namespace AdEntify\CommonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/fbApp/{slug}", requirements={"slug" = "(.+)"})
     * @Template()
     */
    public function facebookAppAction($slug)
    {
        return array();
    }

    /**
     * @Route("/fbApp/")
     * @Template("AdEntifyCommonBundle:Default:facebookApp.html.twig")
     */
    public function facebookIndexAction()
    {
        return array();
    }
}
