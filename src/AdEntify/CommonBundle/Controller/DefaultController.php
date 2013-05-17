<?php

namespace AdEntify\CommonBundle\Controller;

use FOS\OAuthServerBundle\Event\OAuthEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("", name="home_logoff")
     * @Template()
     */
    public function indexAction()
    {
        $securityContext = $this->container->get('security.context');
        if($securityContext->isGranted('IS_AUTHENTICATED_FULLY') ){
            return $this->redirect($this->generateUrl('loggedInHome'));
        }

        return array();
    }

    /**
     * @Route("/app/{slug}", requirements={"slug" = "(.+)"})
     * @Template("AdEntifyCommonBundle:Default:app.html.twig")
     * @Secure("ROLE_USER, ROLE_FACEBOOK, ROLE_TWITTER")
     */
    public function appAllAction($slug)
    {
        return array();
    }

    /**
     * @Route("/app/", name="loggedInHome")
     * @Template("AdEntifyCommonBundle:Default:app.html.twig")
     * @Secure("ROLE_USER, ROLE_FACEBOOK, ROLE_TWITTER")
     */
    public function appIndexAction()
    {
        return array();
    }

    /**
     * @Route("/app/instagram/photos/", name="instagram_photos")
     * @Template("AdEntifyCommonBundle:Default:app.html.twig")
     * @Secure("ROLE_USER, ROLE_FACEBOOK, ROLE_TWITTER")
     */
    public function instagramPhotosAction()
    {
        return array();
    }

    /**
     * @Route("/app/flickr/sets/", name="flickr_sets")
     * @Template("AdEntifyCommonBundle:Default:app.html.twig")
     * @Secure("ROLE_USER, ROLE_FACEBOOK, ROLE_TWITTER")
     */
    public function flickrSetsAction()
    {
        return array();
    }

    /**
     * @Route("/getUserAccessToken", name="get_user_access_token", methods="POST")
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

        $response = new Response(json_encode($accessToken));
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
