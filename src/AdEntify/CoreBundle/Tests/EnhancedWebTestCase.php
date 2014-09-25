<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 19/09/2014
 * Time: 11:39
 */

namespace AdEntify\CoreBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class EnhancedWebTestCase extends WebTestCase
{
    protected function assertJsonResponse($response, $statusCode = 200)
    {
        $this->assertEquals(
            $statusCode, $response->getStatusCode(),
            "Unexpected HTTP status code".PHP_EOL.$response->getContent()
        );
        $this->assertTrue(
            $response->headers->contains('Content-Type', 'application/json'),
            "Not a JSON response".PHP_EOL.$response->headers
        );
    }

    protected function getClient()
    {
        return static::createClient(array(), array('HTTPS' => true));
    }

    protected function getAuthorizationHeader()
    {
        $token = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->createQuery('SELECT token FROM AdEntify\CoreBundle\Entity\OAuth\AccessToken token
                WHERE token.expiresAt > :currentTimestamp')
            ->setMaxResults(1)
            ->setParameters(array(
                'currentTimestamp' => time()
            ))->getOneOrNullResult();

        return sprintf('Bearer %s', $token->getToken());
    }

    protected function assertIsset($decoded, $key)
    {
        $this->assertTrue(isset($decoded[$key]), "Undefined key: ".$key);
    }

    protected function assertPagination($decoded)
    {
        $this->assertTrue(isset($decoded['paging']), "Undefined key: paging");
    }

    protected function assertAllGet($endpoint, $key = null, $authorization = false, $http_code = 200, $has_pagination = false)
    {
        $client = $this->getCLient();

        $http_authorization = ($authorization) ? array('HTTP_Authorization' => $this->getAuthorizationHeader()) : array();

        $client->request('GET', $endpoint, array(), array(), $http_authorization);

        $this->assertEquals($http_code, $client->getResponse()->getStatusCode(), sprintf("Unexpected HTTP status code for GET %s", $endpoint));
        $this->assertJsonResponse($client->getResponse(), $http_code);

        $content = $client->getResponse()->getContent();
        $decoded = json_decode($content, true);

        if (!$key)
            $this->assertTrue(is_array($decoded));
        else if ($http_code == 200)
        {
            $this->assertIsset($decoded, $key);
            if ($has_pagination && !empty($decoded[$key]))
                $this->assertPagination($decoded);
        }
    }
} 