<?php

namespace AdEntify\CoreBundle\Tests\Controller;

use AdEntify\CoreBundle\Tests\EnhancedWebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserControllerTest extends EnhancedWebTestCase
{
    const USER_ID = 1;

    public function testGet()
    {
        // Create a new client to browse the application
        $client = $this->getClient();

        // Create a new entry in the database
        $client->request('GET', sprintf('/api/v1/users/%s', self::USER_ID));
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /api/v1/users/1");
        $this->assertJsonResponse($client->getResponse(), 200);

        $content = $client->getResponse()->getContent();

        $decoded = json_decode($content, true);
        $this->assertTrue(isset($decoded['id']));
    }

    public function testGetCurrent()
    {
        $client = $client = $this->getClient();

        $client->request('GET', '/api/v1/user/current', array(), array(), array(
            'HTTP_Authorization' => $this->getAuthorizationHeader()
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /api/v1/user/current");
        $this->assertJsonResponse($client->getResponse(), 200);

        $content = $client->getResponse()->getContent();

        $decoded = json_decode($content, true);
        $this->assertTrue(isset($decoded['id']));
    }

    public function testGetPhotos()
    {
        // Create a new client to browse the application
        $client = $this->getClient();
        $this->login($client);

        // Create a new entry in the database
        $client->request('GET', sprintf('/api/v1/users/%s/photos', self::USER_ID));
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), sprintf('Unexpected HTTP status code for GET /api/v1/users/%s/photos', self::USER_ID));
        $this->assertJsonResponse($client->getResponse(), 200);

        $content = $client->getResponse()->getContent();

        $decoded = json_decode($content, true);
        $this->assertTrue(isset($decoded['data']));
    }

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
}
