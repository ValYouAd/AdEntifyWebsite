<?php

namespace AdEntify\CoreBundle\Tests\Controller;

use AdEntify\CoreBundle\Tests\EnhancedWebTestCase;

class PhotosControllerTest extends EnhancedWebTestCase
{
    private $options = array();

    public function __construct()
    {
        $this->options['key'] = null;
        $this->options['authorization'] = false;
        $this->options['http_code'] = 200;
        $this->options['has_pagination'] = false;
        $this->options['return_type'] = 'object';
    }

    public function testCget()
    {
        $this->options['key'] = 'data';
        $this->options['has_pagination'] = true;
        $this->options['authorization'] = true;
        $client = $this->getClient();
        $http_authorization = array('HTTP_Authorization' => $this->getAuthorizationHeader());
        $client->request('GET', '/api/v1/photos', array(), array(), $http_authorization);
        echo $client->getResponse()->getContent();
//        $this->assertAllGet('/api/v1/photos', $this->options);
    }
}