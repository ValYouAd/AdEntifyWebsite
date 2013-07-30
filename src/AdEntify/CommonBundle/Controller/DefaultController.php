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
     * @Route("/getUserAccessToken", name="get_user_access_token")
     * @Secure("ROLE_USER, ROLE_FACEBOOK, ROLE_TWITTER")
     */
    public function getUserAccessTokenAction()
    {
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
            $clientManager = $this->container->get('fos_oauth_server.client_manager.default');
            $client = $clientManager->findClientBy(array(
                'id' => 1
            ));

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
