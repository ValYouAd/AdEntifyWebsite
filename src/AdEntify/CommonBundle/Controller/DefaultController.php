<?php

namespace AdEntify\CommonBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DefaultController extends Controller
{
    /**
     * @Route("/app/{slug}", requirements={"slug" = "(.+)"})
     * @Template("AdEntifyCommonBundle:Default:app.html.twig")
     */
    public function appAllAction($slug)
    {
        return array();
    }

    /**
     * @Route("/app/", name="logedInHome")
     * @Template("AdEntifyCommonBundle:Default:app.html.twig")
     */
    public function appIndexAction()
    {
        return array();
    }

    /**
     * @Route("/app/instagram/photos/", name="instagram_photos")
     * @Template("AdEntifyCommonBundle:Default:app.html.twig")
     */
    public function instagramPhotosAction()
    {
        return array();
    }
}
