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
        $this->assertIsset($decoded, 'id');
    }

    public function testGetCurrent()
    {
        $client = $this->getClient();

        $client->request('GET', '/api/v1/user/current', array(), array(), array(
            'HTTP_Authorization' => $this->getAuthorizationHeader()
        ));

        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /api/v1/user/current");
        $this->assertJsonResponse($client->getResponse(), 200);

        $content = $client->getResponse()->getContent();

        $decoded = json_decode($content, true);
        $this->assertIsset($decoded, 'id');
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
        $this->assertIsset($decoded, 'data');
    }

    public function testGetFavorites()
    {
        $client = $this->getCLient();

        $client->request('GET', '/api/v1/user/favorites', array(), array(), array('HTTP_Authorization' => $this->getAuthorizationHeader()));
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), 'Unexpected HTTP status code for GET /api/v1/user/favorites');
        $this->assertJsonResponse($client->getResponse(), 200);

        $content = $client->getResponse()->getContent();

        $decoded = json_decode($content, true);
        $this->assertIsset($decoded, 'data');
    }

    public function testGetSearch()
    {
        $http_method = 'GET';
        $endpoint = '/api/v1/user/search';
        $query_test = ["", "h", "pi", "8", "42", "ak47"];

        $client = $this->getCLient();

        foreach($query_test as $query)
        {
            $http_code = ($query == "") ? 400 : 200;
            $client->request($http_method, sprintf($endpoint.'?query=%s', $query), array(), array(), array('HTTP_Authorization' => $this->getAuthorizationHeader()));
            $this->assertEquals($http_code, $client->getResponse()->getStatusCode(), 'Unexpected HTTP status code for '.$http_method.' '.$endpoint.' with the query '.$query.' .');
            $this->assertJsonResponse($client->getResponse(), $http_code);

            if ($query != "") {
                $content = $client->getResponse()->getContent();

                $decoded = json_decode($content, true);
                $this->assertIsset($decoded, 'data');
            }
        }
    }
}
