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
     * @return string
     */
    public function signRequest($flickrUrl, $key, $secret, $callbackUrl, $type = 'GET')
    {
        $nonce = md5(microtime(true));
        $time = time();

        $datas = array(
            'oauth_callback='.$callbackUrl,
            'oauth_consumer_key='.$key,
            'oauth_nonce='.$nonce,
            'oauth_signature_method=HMAC-SHA1',
            'oauth_timestamp='.$time,
            'oauth_version=1.0',
        );

        $request = $type.'&'.urlencode($flickrUrl).'&'.urlencode(implode('&', $datas));
        $signedRequest = base64_encode(hash_hmac('sha1', $request, $secret.'&', true));

        return array(
            'signature' => $signedRequest,
            'nonce' => $nonce,
            'time' => $time
        );
    }
}