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

    /**
     * @Route("/flickr/request-token", name="flickr_request_token")
     * @Template()
     * @Secure("ROLE_USER, ROLE_FACEBOOK, ROLE_TWITTER")
     */
    public function getRequestTokenAction() {
        $flickrUrl = 'https://www.flickr.com/services/oauth/request_token';
        $callbackUrl = $this->generateUrl('flickr_authent', array(), true);

        $flickrRequestSigner = new FlickrRequestSigner();
        $requestInfos = $flickrRequestSigner->signRequest(
            $flickrUrl,
            $this->container->getParameter('flickr.client_id'),
            $this->container->getParameter('flickr.client_secret'),
            $callbackUrl,
            'GET'
        );
        /*echo 'https://www.flickr.com/services/oauth/request_token?oauth_nonce='
            .urlencode($requestInfos['nonce']).'&oauth_timestamp='.$requestInfos['time']
            .'&oauth_consumer_key='.$this->container->getParameter('flickr.client_id')
            .'&oauth_signature_method=HMAC-SHA1&oauth_version=1.0&oauth_signature='.urlencode($requestInfos['signature'])
            .'&oauth_callback='.urlencode($callbackUrl);die;*/
        return $this->redirect(
            'https://www.flickr.com/services/oauth/request_token?oauth_nonce='
            .urlencode($requestInfos['nonce']).'&oauth_timestamp='.$requestInfos['time']
            .'&oauth_consumer_key='.$this->container->getParameter('flickr.client_id')
            .'&oauth_signature_method=HMAC-SHA1&oauth_version=1.0&oauth_signature='.urlencode($requestInfos['signature'])
            .'&oauth_callback='.urlencode($callbackUrl)
        );
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
        } else if ($this->getRequest()->query->has('code')) {
            // Now we have a code for our AdEntify application, get an access token
            $url = "http://www.flickr.com/services/oauth/request_token";
            $access_token_parameters = array(
                'client_id'		=>     $this->container->getParameter('flickr.client_id'),
                'client_secret' =>     $this->container->getParameter('flickr.client_secret'),
                'redirect_uri'	=>     $this->generateUrl('flickr_authent', array(), true),
                'code'			=>     $this->getRequest()->query->get('code')
            );
            $curl = curl_init($url);
            curl_setopt($curl,CURLOPT_POST, true);
            curl_setopt($curl,CURLOPT_POSTFIELDS, $access_token_parameters);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $json = json_decode(curl_exec($curl));

            // Save flickr infos
            $loggedInUser = $this->container->get('security.context')->getToken()->getUser();
            $em = $this->getDoctrine()->getManager();

            $oAuthUserInfo = $em->getRepository('AdEntifyCoreBundle:OAuthUserInfo')->findOneBy(array(
                'serviceUserId' => $json->user->id,
                'serviceName' => self::SERVICE_NAME
            ));
            $exist = $oAuthUserInfo ? true : false;

            $oAuthUserInfo = $exist ? $oAuthUserInfo : new OAuthUserInfo();
            $oAuthUserInfo
                ->setUser($loggedInUser)
                ->setServiceAccessToken($json->access_token)
                ->setServiceName(self::SERVICE_NAME)
                ->setServiceUsername($json->user->username)
                ->setServiceFullName($json->user->full_name)
                ->setServiceUserId($json->user->id);

            if ($exist) {
                $em->merge($oAuthUserInfo);
            } else {
                $em->persist($oAuthUserInfo);
            }
            $em->flush();

            return $this->redirect($this->generateUrl('flickr_photos'));
        }
        else {
            throw new AuthenticationException('Can\'t get Instagram feed');
        }
    }
}
