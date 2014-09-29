<?php

namespace AdEntify\CoreBundle\Tests\Controller;

use AdEntify\CoreBundle\Tests\EnhancedWebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class UserControllerTest extends EnhancedWebTestCase
{
    const USER_ID = 1;

    private $options = array();
    private $users = array(1, 50000000);
    private $dates = array("2014-09-08", "2014-09-09");

    public function __construct()
    {
        $this->options['key'] = null;
        $this->options['authorization'] = false;
        $this->options['http_code'] = 200;
        $this->options['has_pagination'] = false;
        $this->options['return_type'] = 'object';
    }

    public function testGet()
    {
        $this->options['key'] = 'id';
        $this->assertAllGet(sprintf('/api/v1/users/%s', self::USER_ID), $this->options);
    }

    public function testGetCurrent()
    {
        $this->options['key'] = 'id';
        $this->options['authorization'] = true;
        $this->assertAllGet('/api/v1/user/current', $this->options);
    }

    public function testGetPhotos()
    {
        $this->options['key'] = 'data';
        $this->options['has_pagination'] = true;
        foreach($this->users as $user)
        {
            if ($user != 1)
                $this->options['authorization'] = true;
            $this->assertAllGet(sprintf('/api/v1/users/%s/photos', $user), $this->options);
        }
    }

    public function testGetFavorites()
    {
        $this->options['key'] = 'data';
        $this->options['authorization'] = true;
        $this->assertAllGet('/api/v1/user/favorites', $this->options);
    }

    public function testGetSearch()
    {
        $query_test = ["", "h", "pi", "8", "42", "ak47"];
        $this->options['key'] = 'data';
        $this->options['authorization'] = true;
        $this->options['has_pagination'] = true;
        foreach($query_test as $query)
        {
            $this->options['http_code'] = ($query == "") ? 400 : 200;
            $this->assertAllGet(sprintf('/api/v1/user/search?query=%s', $query), $this->options);
        }
    }
    public function testGetIsFollowed()
    {
        $this->options['key'] = 'followed';
        $this->options['authorization'] = true;
        foreach($this->users as $user)
            $this->assertAllGet(sprintf('/api/v1/users/%s/is/followed', $user), $this->options);
    }

    public function testGetLikedPhotos()
    {
        $this->options['key'] = 'data';
        $this->options['has_pagination'] = true;
        foreach($this->users as $user)
            $this->assertAllGet(sprintf('/api/v1/users/%s/liked/photos', $user), $this->options);
    }

    public function testGetHashtags()
    {
        $this->options['key'] = 'data';
        $this->options['has_pagination'] = true;
        foreach($this->users as $user)
            $this->assertAllGet(sprintf('/api/v1/users/%s/hashtags', $user), $this->options);
    }

    public function testGetNotifications()
    {
        $this->options['authorization'] = true;
        $this->options['return_type'] = 'array';
        $this->assertAllGet('/api/v1/user/notifications', $this->options);
    }

    public function testGetFollowings()
    {
        $this->options['key'] = 'data';
        $this->options['authorization'] = true;
        $this->options['has_pagination'] = true;
        $this->assertAllGet(sprintf('/api/v1/users/%s/followings', self::USER_ID), $this->options);
    }

    public function testGetFollowers()
    {
        $this->options['key'] = 'data';
        $this->options['authorization'] = true;
        $this->options['has_pagination'] = true;
        $this->assertAllGet(sprintf('/api/v1/users/%s/followers', self::USER_ID), $this->options);
    }

    public function testGetTopFollowers()
    {
        $this->options['return_type'] = 'array';
        $this->assertAllGet('/api/v1/user/top/followers', $this->options);
    }

    public function testGetTaskProgress()
    {
        $this->options['return_type'] = 'percentage';
        $this->options['authorization'] = true;
        $this->assertAllGet('/api/v1/users/progresses/task', $this->options);
    }

    public function testGetBrands()
    {
        $this->options['key'] = 'data';
        $this->options['authorization'] = true;
        $this->options['has_pagination'] = true;
        $this->assertAllGet('/api/v1/user/brands', $this->options);
    }

    public function testGetAnalytics()
    {
        $this->options['key'] = 'taggedPhotos';
        $this->options['authorization'] = true;
        $this->assertAllGet('/api/v1/user/analytics', $this->options);
    }

    public function testGetPoints()
    {
        $this->options['return_type'] = 'integer';
        $this->options['authorization'] = true;
        $this->assertAllGet('api/v1/user/points', $this->options);
    }

    public function testGetCreditsByDateRange()
    {
        $this->options['return_type'] = 'array';
        $this->options['authorization'] = true;
        $this->assertAllGet('api/v1/user/credits/by/date/range', $this->options);
    }

    public function testGetDashboard()
    {
        $endpoint = 'api/v1/user/dashboard';

        $client = $this->getCLient();
        $client->request('GET', $endpoint, array(), array(), array('HTTP_Authorization' => $this->getAuthorizationHeader()));

        $this->assertJsonResponse($client->getResponse(), $this->options['http_code'], $endpoint);

        $content = $client->getResponse()->getContent();
        $decoded = json_decode($content, true);

        $this->assertIsset($decoded['actions'], 'data');
        if (!empty($decoded['actions']['data']))
            $this->assertPagination($decoded['actions']);
    }

    public function testGetCreditsByDate()
    {
        $this->options['return_type'] = 'array';
        $this->options['authorization'] = true;
        foreach($this->dates as $date)
            $this->assertAllGet(sprintf('api/v1/users/%s/credits/by/date', $date), $this->options);
    }

    public function testGetRewards()
    {
        $this->options['key'] = 'data';
        $this->options['has_pagination'] = true;
        $this->assertAllGet(sprintf('api/v1/users/%s/rewards', self::USER_ID), $this->options);
    }

    public function testGetActions()
    {
        $this->options['authorization'] = true;
        $this->options['key'] = 'data';
        $this->options['has_pagination'] = true;
        $this->assertAllGet('api/v1/user/actions', $this->options);
    }
}