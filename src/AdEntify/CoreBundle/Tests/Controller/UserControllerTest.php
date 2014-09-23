<?php

namespace AdEntify\CoreBundle\Tests\Controller;

use AdEntify\CoreBundle\Tests\EnhancedWebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserControllerTest extends EnhancedWebTestCase
{
    const USER_ID = 1;

    /**
     * @param $client
     */
    private function logIn($client)
    {
        $session = $client->getContainer()->get('session');

        $firewall = 'secured_area';
        $token = new UsernamePasswordToken('admin', null, $firewall, array('ROLE_ADMIN'));
        $session->set('_security_'.$firewall, serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);
    }

    public function assertAllGet($endpoint, $key, $authorization, $http_code = 200)
    {
        $client = $this->getCLient();

        $http_authorization = ($authorization) ? array('HTTP_Authorization' => $this->getAuthorizationHeader()) : array();

        $client->request('GET', $endpoint, array(), array(), $http_authorization);
        $this->assertEquals($http_code, $client->getResponse()->getStatusCode(), sprintf("Unexpected HTTP status code for GET %s", $endpoint));
        $this->assertJsonResponse($client->getResponse(), $http_code);

        $content = $client->getResponse()->getContent();

        if ($http_code != 400)
        {
            $decoded = json_decode($content, true);
            $this->assertIsset($decoded, $key);
        }
    }

    public function testGet()
    {
        $this->assertAllGet(sprintf('/api/v1/users/%s', self::USER_ID), 'id', false);
    }

    public function testGetCurrent()
    {
        $this->assertAllGet(sprintf('/api/v1/user/current'), 'id', true);
    }

    public function testGetPhotos()
    {
        $this->assertAllGet(sprintf('/api/v1/users/%s/photos', self::USER_ID), 'data', false);
    }

    public function testGetFavorites()
    {
        $this->assertAllGet('/api/v1/user/favorites', 'data', true);
    }

    public function testGetSearch()
    {
        $query_test = ["", "h", "pi", "8", "42", "ak47"];
        foreach($query_test as $query)
        {
            $http_code = ($query == "") ? 400 : 200;
            $this->assertAllGet(sprintf('/api/v1/user/search?query=%s', $query), 'data', true, $http_code);
        }
    }
    public function testGetIsFollowed()
    {
        $this->assertAllGet('/api/v1/user/favorites', 'data', true);
    }
}
