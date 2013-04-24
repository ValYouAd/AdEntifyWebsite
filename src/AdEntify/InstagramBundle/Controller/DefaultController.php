<?php

namespace AdEntify\InstagramBundle\Controller;

use AdEntify\CoreBundle\Entity\InstagramInfo;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\Secure;

class DefaultController extends Controller
{
    /**
     * @Route("/instagram/authentication", name="instagram_authent")
     * @Template()
     */
    public function indexAction()
    {
        if ($this->getRequest()->query->has('error')) {
            return array(
                'error' => true,
                'error_reason' => $this->getRequest()->query->get('error_reason'),
                'error_description' => $this->getRequest()->query->get('error_description'),
            );
        } else if ($this->getRequest()->query->has('code')) {
            // Now we have a code for our AdEntify application, get an access token
            $url = "https://api.instagram.com/oauth/access_token";
            $access_token_parameters = array(
                'client_id'		=>     'db51217592cf490aab08224763fc6cea',
                'client_secret'		=>     '48017f2c3cf441d6996ca7813e1abeb9',
                'grant_type'		=>     'authorization_code',
                'redirect_uri'		=>     'http://localhost/AdEntifyFacebookApp/web/instagram/authentication',
                'code'			=>     $this->getRequest()->query->get('code')
            );
            $curl = curl_init($url);
            curl_setopt($curl,CURLOPT_POST,true);
            curl_setopt($curl,CURLOPT_POSTFIELDS,$access_token_parameters);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($curl);

            $json = json_decode($result);

            $url = 'https://api.instagram.com/v1/users/self/feed?access_token='.$json->access_token;

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec($curl);

            $json2 = json_decode($result, true);

            //print_r($json2);

            return array('image' => 'http://distilleryimage1.s3.amazonaws.com/dcb5eed6a61d11e28e2d22000a1fbe71_7.jpg');


            //$em = $this->getDoctrine()->getManager();

            //$user = $em->getRepository('AdEntifyCoreBundle:User')->find(1);

           /* print_r($json);

            $instagramInfos = new InstagramInfo();
            $instagramInfos->setInstagramAccessToken($json->access_token)
                ->setInstagramFullName($json->user->full_name)
                ->setInstagramId($json->user->id)
                ->setInstagramUsername($json->user->username);*/

            /*$user->setInstagramInfo($instagramInfos);


            $em->persist($instagramInfos);
            $em->merge($user);
            $em->flush();*/

            print_r($instagramInfos);
        }
        else {
            return array(
                'error' => true,
                'error_reason' => 'Erreur inconnue',
                'error_description' => '',
            );
        }
    }

    /**
     * @Route("/instagram/access-token", name="instagram_access_token")
     *
     */
    public function tokenAction()
    {
        return new Response('');
    }
}
