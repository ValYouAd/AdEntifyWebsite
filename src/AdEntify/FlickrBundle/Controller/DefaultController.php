<?php

namespace AdEntify\FlickrBundle\Controller;

use AdEntify\CoreBundle\Entity\OAuthUserInfo;
use AdEntify\FlickrBundle\Util\FlickrRequestSigner;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class DefaultController extends Controller
{
    const SERVICE_NAME = 'flickr';
    const SESSION_REQUEST_TOKEN = 'flickr-request-token';
    const SESSION_REQUEST_TOKEN_SECRET = 'flickr-request-token-secret';

    /**
     * @Route("/flickr/request-token", name="flickr_request_token")
     * @Template()
     * @Secure("ROLE_USER, ROLE_FACEBOOK, ROLE_TWITTER")
     */
    public function getRequestTokenAction() {
        $loggedInUser = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $oAuthUserInfo = $em->getRepository('AdEntifyCoreBundle:OAuthUserInfo')->findOneBy(array(
            'user' => $loggedInUser->getId(),
            'serviceName' => self::SERVICE_NAME
        ));

        if ($oAuthUserInfo)
            return $this->redirect($this->generateUrl('flickr_sets'));

        $flickrUrl = 'http://www.flickr.com/services/oauth/request_token';
        $callbackUrl = $this->generateUrl('flickr_authent', array(), true);

        // Sign request
        $flickrRequestSigner = new FlickrRequestSigner();
        $requestInfos = $flickrRequestSigner->signRequest(
            $flickrUrl,
            $this->container->getParameter('flickr.client_id'),
            $this->container->getParameter('flickr.client_secret'),
            array(
                'oauth_callback=' => urlencode($callbackUrl),
            ),
            'GET'
        );

        // Get request token
        $requestTokenUrl = $flickrUrl.'?oauth_nonce='
            .urlencode($requestInfos['nonce']).'&oauth_timestamp='.$requestInfos['time']
            .'&oauth_consumer_key='.$this->container->getParameter('flickr.client_id')
            .'&oauth_signature_method=HMAC-SHA1&oauth_version=1.0&oauth_signature='.urlencode($requestInfos['signature'])
            .'&oauth_callback='.urlencode($callbackUrl);
        $ch = curl_init();
        $timeout = 5;
        curl_setopt ($ch, CURLOPT_URL, $requestTokenUrl);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $response = curl_exec($ch);
        curl_close($ch);

        // Parse flickr response
        parse_str($response, $output);

        if (array_key_exists('oauth_callback_confirmed', $output) && $output['oauth_callback_confirmed'] == true) {
            // Save token in session
            $session = $this->getRequest()->getSession();
            $session->set(self::SESSION_REQUEST_TOKEN, $output['oauth_token']);
            $session->set(self::SESSION_REQUEST_TOKEN_SECRET,  $output['oauth_token_secret']);

            // Redirect user to authorize page
            return $this->redirect(
                'http://www.flickr.com/services/oauth/authorize?oauth_token='.$output['oauth_token']
            );
        } else {
            throw new AuthenticationException('Can\'t get Flickr feed');
        }
    }

    /**
     * @Route("/flickr/authentication", name="flickr_authent")
     * @Template()
     * @Secure("ROLE_USER, ROLE_FACEBOOK, ROLE_TWITTER")
     */
    public function indexAction()
    {
        if ($this->getRequest()->query->has('error')) {
            throw new AuthenticationException($this->getRequest()->query->get('error_reason'));
        } else if ($this->getRequest()->query->has('oauth_token') &&
                    $this->getRequest()->query->has('oauth_verifier')) {

            // Get token, token secret & verifier
            $session = $this->getRequest()->getSession();
            $token = $this->getRequest()->query->get('oauth_token');
            $verifier = $this->getRequest()->query->get('oauth_verifier');
            $tokenSecret = $session->get(self::SESSION_REQUEST_TOKEN_SECRET);

            // Check if token is the same that token in session
            if ($token == $session->get(self::SESSION_REQUEST_TOKEN)) {
                // Delete session
                $session->remove(self::SESSION_REQUEST_TOKEN);

                // Exchange request token for an access token
                $flickrUrl = 'http://www.flickr.com/services/oauth/access_token';

                // Sign request
                $flickrRequestSigner = new FlickrRequestSigner();
                $requestInfos = $flickrRequestSigner->signRequest(
                    $flickrUrl,
                    $this->container->getParameter('flickr.client_id'),
                    $this->container->getParameter('flickr.client_secret'),
                    array(
                        'oauth_token=' => $token,
                        'oauth_verifier=' => $verifier,
                    ),
                    $tokenSecret,
                    'GET'
                );

                // Get access token
                $accessTokenUrl = $flickrUrl.'?oauth_nonce='
                    .urlencode($requestInfos['nonce']).'&oauth_timestamp='.$requestInfos['time']
                    .'&oauth_consumer_key='.$this->container->getParameter('flickr.client_id')
                    .'&oauth_signature_method=HMAC-SHA1&oauth_version=1.0&oauth_signature='.urlencode($requestInfos['signature'])
                    .'&oauth_verifier='.$verifier.'&oauth_token='.$token;
                $ch = curl_init();
                $timeout = 5;
                curl_setopt ($ch, CURLOPT_URL, $accessTokenUrl);
                curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                $response = curl_exec($ch);
                curl_close($ch);

                // Parse flickr response
                parse_str($response, $output);

                if (array_key_exists('fullname', $output)) {
                    // Save flickr infos
                    $loggedInUser = $this->container->get('security.context')->getToken()->getUser();
                    $em = $this->getDoctrine()->getManager();

                    $oAuthUserInfo = $em->getRepository('AdEntifyCoreBundle:OAuthUserInfo')->findOneBy(array(
                        'serviceUserId' => urldecode($output['user_nsid']),
                        'serviceName' => self::SERVICE_NAME
                    ));
                    $exist = $oAuthUserInfo ? true : false;

                    $oAuthUserInfo = $exist ? $oAuthUserInfo : new OAuthUserInfo();
                    $oAuthUserInfo
                        ->setUser($loggedInUser)
                        ->setServiceAccessToken($output['oauth_token'])
                        ->setServiceAccessTokenSecret($output['oauth_token_secret'])
                        ->setServiceName(self::SERVICE_NAME)
                        ->setServiceUsername(urldecode($output['username']))
                        ->setServiceFullName(urldecode($output['fullname']))
                        ->setServiceUserId(urldecode($output['user_nsid']));

                    if ($exist)
                        $em->merge($oAuthUserInfo);
                    else
                        $em->persist($oAuthUserInfo);
                    $em->flush();

                    return $this->redirect($this->generateUrl('flickr_sets'));
                } else {
                    throw new AuthenticationException('Can\'t get Flickr feed');
                }
            } else {
                throw new AuthenticationException('Can\'t get Flickr feed');
            }
        }
        else {
            throw new AuthenticationException('Can\'t get Flickr feed');
        }
    }

    /**
     * @Route("/flickr/sets/photos/{id}", name="flickr_sets_photos")
     * @Template()
     * @Secure("ROLE_USER, ROLE_FACEBOOK, ROLE_TWITTER")
     */
    public function getPhotosAction($id)
    {
        $loggedInUser = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $oAuthUserInfo = $em->getRepository('AdEntifyCoreBundle:OAuthUserInfo')->findOneBy(array(
            'user' => $loggedInUser->getId(),
            'serviceName' => self::SERVICE_NAME
        ));

        if ($oAuthUserInfo) {
            // Exchange request token for an access token
            $flickrUrl = 'http://api.flickr.com/services/rest/';

            // Sign request
            $flickrRequestSigner = new FlickrRequestSigner();
            $requestInfos = $flickrRequestSigner->signRequest(
                $flickrUrl,
                $this->container->getParameter('flickr.client_id'),
                $this->container->getParameter('flickr.client_secret'),
                array(
                    'extras=' => 'original_format,o_dims,views,media,path_alias,url_sq,url_t,url_s,url_m,url_o',
                    'photoset_id=' => $id,
                    'oauth_token=' => $oAuthUserInfo->getServiceAccessToken(),
                    'privacy_filter=' => '1,2,3,4,5'
                ),
                '',
                'GET'
            );

            // Get photos of set
            $accessTokenUrl = $flickrUrl.'?method=flickr.photosets.getPhotos&nojsoncallback=1&format=json&oauth_nonce='
                .urlencode($requestInfos['nonce']).'&oauth_timestamp='.$requestInfos['time']
                .'&oauth_consumer_key='.$this->container->getParameter('flickr.client_id')
                .'&oauth_signature_method=HMAC-SHA1&oauth_version=1.0&oauth_signature='.urlencode($requestInfos['signature'])
                .'&oauth_token='.$oAuthUserInfo->getServiceAccessToken().'&extras=original_format,o_dims,views,media,path_alias,url_sq,url_t,url_s,url_m,url_o'
                .'&photoset_id='.$id.'&privacy_filter=1,2,3,4,5';

            $ch = curl_init();
            $timeout = 5;
            curl_setopt ($ch, CURLOPT_URL, $accessTokenUrl);
            curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $response = json_decode(curl_exec($ch));
            curl_close($ch);

            $response = new Response(json_encode($response->photoset->photo));
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        } else {
            throw new AuthenticationException('Can\'t get Flickr feed');
        }
    }
}
