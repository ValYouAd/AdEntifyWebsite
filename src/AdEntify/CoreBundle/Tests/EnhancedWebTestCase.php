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
    protected function assertJsonResponse($response, $statusCode = 200, $endpoint)
    {
        $this->assertEquals(
            $statusCode, $response->getStatusCode(),
            sprintf("Unexpected HTTP status code for GET %s", $endpoint)
        );
        $this->assertTrue(
            $response->headers->contains('Content-Type', 'application/json'),
            sprintf("Not a JSON response\n%s", $response->headers)
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
        $this->assertTrue(isset($decoded[$key]), sprintf("Undefined key: %s", $key));
    }

    protected function assertPagination($decoded)
    {
        $this->assertTrue(isset($decoded['paging']), sprintf("Undefined key: paging"));
    }

    protected function assertAllGet($endpoint, $options)
    {
        $client = $this->getCLient();
        $http_authorization = ($options['authorization']) ? array('HTTP_Authorization' => $this->getAuthorizationHeader()) : array();
        $client->request('GET', $endpoint, array(), array(), $http_authorization);

        $this->assertJsonResponse($client->getResponse(), $options['http_code'], $endpoint);

        $content = $client->getResponse()->getContent();
        $decoded = json_decode($content, true);

        switch($options['return_type'])
        {
            case 'object':
                if ($options['http_code'] == 200) {
                    $this->assertIsset($decoded, $options['key']);
                    if ($options['has_pagination'] && !empty($decoded[$options['key']]))
                        $this->assertPagination($decoded);
                }
                break;
            case 'array':
                $this->assertTrue(is_array($decoded));
                break;
            case 'percentage':
                $this->assertTrue($decoded <= 100 && $decoded >= 0);
                break;
            case 'integer':
                $this->assertTrue(gettype($decoded) == 'integer');
                break;
        }
    }
} 