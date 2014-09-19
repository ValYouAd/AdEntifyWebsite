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
            $response->getContent()
        );
        $this->assertTrue(
            $response->headers->contains('Content-Type', 'application/json'),
            $response->headers
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
} 