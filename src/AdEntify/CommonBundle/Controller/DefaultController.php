<?php

namespace AdEntify\CommonBundle\Controller;

use AdEntify\CoreBundle\Util\FileTools;
use Doctrine\Tests\Common\Annotations\False;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="root_url")
     */
    public function indexNoLocaleAction()
    {
        return $this->redirect($this->generateUrl('home_logoff', array(
            '_locale' => $this->getCurrentLocale()
        )));
    }

    /**
     * @Route("{_locale}/", defaults={"_locale" = "fr"}, requirements={"_locale" = "en|fr"}, name="home_logoff")
     * @Template
     */
    public function indexAction()
    {
        $securityContext = $this->container->get('security.context');
        if($securityContext->isGranted('IS_AUTHENTICATED_FULLY') ){
            return $this->redirect($this->generateUrl('loggedInHome', array(
                '_locale' => $this->getCurrentLocale()
            )));
        }

        return array();
    }

    /**
     * @Route("/{_locale}/app/{slug}", defaults={"_locale" = "fr"}, requirements={"_locale" = "en|fr","slug" = "(.+)"})
     * @Template("AdEntifyCommonBundle:Default:app.html.twig")
     * @Secure("ROLE_USER, ROLE_FACEBOOK, ROLE_TWITTER")
     */
    public function appAllAction($slug)
    {
        $categories = $this->getDoctrine()->getManager()
            ->createQuery("SELECT category FROM AdEntify\CoreBundle\Entity\Category category")
            ->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, $this->getRequest()->getLocale())
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_FALLBACK, 1)
            ->getResult();

        return array(
            'categories' => $categories
        );
    }

    /**
     * @Route("/{_locale}/app/", name="loggedInHome", defaults={"_locale" = "fr"}, requirements={"_locale" = "en|fr"})
     * @Template("AdEntifyCommonBundle:Default:app.html.twig")
     * @Secure("ROLE_USER, ROLE_FACEBOOK, ROLE_TWITTER")
     */
    public function appIndexAction()
    {
        $categories = $this->getDoctrine()->getManager()
            ->createQuery("SELECT category FROM AdEntify\CoreBundle\Entity\Category category")
            ->setHint(\Doctrine\ORM\Query::HINT_CUSTOM_OUTPUT_WALKER, 'Gedmo\\Translatable\\Query\\TreeWalker\\TranslationWalker')
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_TRANSLATABLE_LOCALE, $this->getRequest()->getLocale())
            ->setHint(\Gedmo\Translatable\TranslatableListener::HINT_FALLBACK, 1)
            ->getResult();

        return array(
            'categories' => $categories
        );
    }

    /**
     * @Route("/app/")
     * @Template("AdEntifyCommonBundle:Default:app.html.twig")
     */
    public function appNoLocaleAction()
    {
        return $this->redirect($this->generateUrl('loggedInHome', array(
            '_locale' => $this->getCurrentLocale()
        )));
    }

    /**
     * @Route("/{_locale}/app/instagram/photos/", name="instagram_photos")
     * @Template("AdEntifyCommonBundle:Default:app.html.twig")
     * @Secure("ROLE_USER, ROLE_FACEBOOK, ROLE_TWITTER")
     */
    public function instagramPhotosAction()
    {
        return array();
    }

    /**
     * @Route("/{_locale}/app/flickr/sets/", name="flickr_sets")
     * @Template("AdEntifyCommonBundle:Default:app.html.twig")
     * @Secure("ROLE_USER, ROLE_FACEBOOK, ROLE_TWITTER")
     */
    public function flickrSetsAction()
    {
        return array();
    }

    /**
     * @Route("/lang/{locale}", requirements={"locale" = "en|fr"}, name="change_lang")
     */
    public function langAction($locale)
    {
        $this->getRequest()->getSession()->set('_locale', $locale);
        $this->setUserLocale($locale);
        return $this->redirect($this->generateUrl('loggedInHome', array(
            '_locale' => $locale
        )));
    }

    /**
     * @Route("/r/{id}", name="redirect_url")
     */
    public function redirectAction($id)
    {
        $shortUrl = $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:ShortUrl')
            ->findByBase64IdAndUpdateCounter($id);
        if ($shortUrl !== false) {
            return $this->redirect($shortUrl->getUrl(), 301);
        } else {
            throw new NotFoundHttpException('Redirect url not found');
        }
    }

    /**
     * @Route("/test")
     */
    public function testAction()
    {
        $clientManager = $this->container->get('fos_oauth_server.client_manager.default');
        $client = $clientManager->findClientBy(array(
            'id' => 1
        ));
        return $this->redirect($this->generateUrl('fos_oauth_server_authorize', array(
            'client_id'     => $client->getPublicId(),
            'redirect_uri'  => 'http://localhost/AdEntifyFacebookApp/web/',
            'response_type' => 'code'
        )));
    }

    /**
     * Get current locale from user if logged and set, instead, get from request
     *
     * @return string
     */
    private function getCurrentLocale() {
        $locale = $this->getRequest()->getLocale();
        $securityContext = $this->container->get('security.context');
        if($securityContext->isGranted('IS_AUTHENTICATED_FULLY') ){
            $user = $this->container->get('security.context')->getToken()->getUser();
            if ($user->getLocale()) {
                $locale = $user->getLocale();
            }
        }

        return $locale;
    }

    /**
     * Set locale for the current user if logged
     *
     * @param $locale
     */
    private function setUserLocale($locale) {
        $securityContext = $this->container->get('security.context');
        if($securityContext->isGranted('IS_AUTHENTICATED_FULLY') ){
            $user = $this->container->get('security.context')->getToken()->getUser();
            $user->setLocale($locale);
            $this->getDoctrine()->getManager()->merge($user);
            $this->getDoctrine()->getManager()->flush();
        }
    }
}
