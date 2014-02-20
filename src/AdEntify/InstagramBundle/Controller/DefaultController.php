<?php

namespace AdEntify\InstagramBundle\Controller;

use AdEntify\CoreBundle\Entity\InstagramInfo;
use AdEntify\CoreBundle\Entity\OAuthUserInfo;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class DefaultController extends Controller
{
    const SERVICE_NAME = 'instagram';

    /**
     * @Route("/instagram/authentication", name="instagram_authent")
     * @Template()
     * @Secure("ROLE_USER, ROLE_FACEBOOK, ROLE_TWITTER")
     */
    public function indexAction()
    {
        if ($this->getRequest()->query->has('error')) {
            throw new AuthenticationException($this->getRequest()->query->get('error_reason'));
        } else if ($this->getRequest()->query->has('code')) {
            // Now we have a code for our AdEntify application, get an access token
            $url = "https://api.instagram.com/oauth/access_token";
            $access_token_parameters = array(
                'client_id'		=>     $this->container->getParameter('instagram.client_id'),
                'client_secret'		=>     $this->container->getParameter('instagram.client_secret'),
                'grant_type'		=>     'authorization_code',
                'redirect_uri'		=>     $this->generateUrl('instagram_authent', array(), true),
                'code'			=>     $this->getRequest()->query->get('code')
            );
            $curl = curl_init($url);
            curl_setopt($curl,CURLOPT_POST, true);
            curl_setopt($curl,CURLOPT_POSTFIELDS, $access_token_parameters);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $json = json_decode(curl_exec($curl));

            // Save instagram infos
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

            return $this->redirect($this->generateUrl('instagram_photos', array(
                '_locale' => $loggedInUser->getLocale() ? $loggedInUser->getLocale() : 'en'
                )
            ));
        }
        else {
            throw new AuthenticationException('Can\'t get Instagram feed');
        }
    }
}
