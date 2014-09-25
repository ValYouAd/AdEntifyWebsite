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

    private function getTestUsers()
    {
        return array(1, 2, 3, 4, 5);
    }

    public function testGet()
    {
        $this->assertAllGet(sprintf('/api/v1/users/%s', self::USER_ID), 'id');
    }

    public function testGetCurrent()
    {
        $this->assertAllGet(sprintf('/api/v1/user/current'), 'id', true);
    }

    public function testGetPhotos()
    {
        $this->assertAllGet(sprintf('/api/v1/users/%s/photos', self::USER_ID), 'data');
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
            $this->assertAllGet(sprintf('/api/v1/user/search?query=%s', $query), 'data', true, $http_code, true);
        }
    }
    public function testGetIsFollowed()
    {
        foreach($this->getTestUsers() as $user)
            $this->assertAllGet(sprintf('/api/v1/users/%s/is/followed', $user), 'followed', true);
    }

    public function testGetLikedPhotos()
    {
        foreach($this->getTestUsers() as $user)
            $this->assertAllGet(sprintf('/api/v1/users/%s/liked/photos', $user), 'data', false, 200, true);
    }

    public function testGetHashtags()
    {
        foreach($this->getTestUsers() as $user)
            $this->assertAllGet(sprintf('/api/v1/users/%s/hashtags', $user), 'data', false, 200, true);
    }

    public function testGetNotifications()
    {
        $this->assertAllGet(sprintf('/api/v1/user/notifications'), null, true);
    }

    public function testGetFollowings()
    {
        $this->assertAllGet(sprintf('/api/v1/users/%s/followings', self::USER_ID), 'data', true, 200, true);
    }

    public function testGetFollowers()
    {
        $this->assertAllGet(sprintf('/api/v1/users/%s/followers', self::USER_ID), 'data', true, 200, true);
    }

    public function testGetTopFollowers()
    {
        $this->assertAllGet('/api/v1/user/top/followers');
    }

    public function testTaskProgress()
    {
//        $this->assertAllGet('/api/v1/users/progresses/task', );
    }
//    protected function assertAllGet($endpoint, $key, $authorization = false, $http_code = 200, $has_pagination = false)
}