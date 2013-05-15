<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 07/05/2013
 * Time: 15:08
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\FlickrBundle\Util;


class FlickrRequestSigner
{
    /**
     * @param $flickrUrl
     * @param $key
     * @param $secret
     * @param $callbackUrl
     * @param string $type
     * @return array
     */
    /*public function signRequestTokenRequest($flickrUrl, $key, $secret, $callbackUrl, $type = 'GET')
    {
        $nonce = md5(microtime(true));
        $time = time();

        $datas = array(
            'oauth_callback='.urlencode($callbackUrl),
            'oauth_consumer_key='.$key,
            'oauth_nonce='.$nonce,
            'oauth_signature_method=HMAC-SHA1',
            'oauth_timestamp='.$time,
            'oauth_version=1.0',
        );

        $signedRequest = $this->hashRequest($flickrUrl, $datas, $secret, $type);

        return array(
            'signature' => $signedRequest,
            'nonce' => $nonce,
            'time' => $time
        );
    }*/

    /**
     * @param $flickrUrl
     * @param $key
     * @param $secret
     * @param $token
     * @param $verifier
     * @param string $type
     * @return array
     */
    /*public function signAccessTokenRequest($flickrUrl, $key, $secret, $token, $tokenSecret, $verifier, $type = 'GET')
    {
        $nonce = md5(microtime(true));
        $time = time();

        $datas = array(
            'oauth_consumer_key='.$key,
            'oauth_nonce='.$nonce,
            'oauth_signature_method=HMAC-SHA1',
            'oauth_timestamp='.$time,
            'oauth_token='.$token,
            'oauth_verifier='.$verifier,
            'oauth_version=1.0',
        );

        $signedRequest = $this->hashRequest($flickrUrl, $datas, $secret, $type, $tokenSecret);

        return array(
            'signature' => $signedRequest,
            'nonce' => $nonce,
            'time' => $time
        );
    }*/

    public function signRequest($flickrUrl, $key, $secret, $params = array(), $type = 'GET', $tokenSecret = '')
    {
        $nonce = md5(microtime(true));
        $time = time();

        $datas = array(
            'oauth_consumer_key=' => $key,
            'oauth_nonce=' => $nonce,
            'oauth_signature_method=' => 'HMAC-SHA1',
            'oauth_timestamp=' => $time,
            'oauth_version=' => '1.0',
        );

        if (count($params)) {
            $datas = array_merge($datas, $params);
        }
        ksort($datas);

        $signedRequest = $this->hashRequest($flickrUrl, $datas, $secret, $type, $tokenSecret);

        return array(
            'signature' => $signedRequest,
            'nonce' => $nonce,
            'time' => $time
        );
    }

    /**
     * @param $flickrUrl
     * @param $datas
     * @param $secret
     * @param string $type
     * @return string
     */
    private function hashRequest($flickrUrl, $datas, $secret, $type = 'GET', $tokenSecret = '')
    {
        $joinedString = '';
        foreach($datas as $key => $value) {
            $joinedString .= $key.$value.'&';
        }
        $joinedString = substr($joinedString, 0, strlen($joinedString) -1);

        $request = $type.'&'.urlencode($flickrUrl).'&'.urlencode($joinedString);
        $signedRequest = base64_encode(hash_hmac('sha1', $request, $secret.'&'.$tokenSecret, true));

        return $signedRequest;
    }
}