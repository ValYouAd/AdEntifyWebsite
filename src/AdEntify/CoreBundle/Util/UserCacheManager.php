<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 26/09/2013
 * Time: 16:00
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Util;


use AdEntify\CoreBundle\Entity\User;

class UserCacheManager extends CacheManager
{
    const USER_CACHE_KEY_FB_FRIENDS = 'facebook.friends';
    const USER_CACHE_KEY_FOLLOWINGS = 'followings';
    const USER_CACHE_KEY_BRAND_FOLLOWINGS = 'brand.followings';

    const USER_CACHE_TTL_FOLLOWING = 172800; // in seconds, 48h
    const USER_CACHE_TTL_FB_FRIENDS = 172800; // in seconds, 48h
    const USER_CACHE_TTL_BRAND_FOLLOWINGS = 172800; // in seconds, 48h

    public function getUserObject(User $user, $key)
    {
        return $this->getObject($this->getUserKey($user, $key));
    }

    public function setUserObject(User $user, $key, $value, $ttl)
    {
        return $this->setObject($this->getUserKey($user, $key), $value, $ttl);
    }

    public function deleteUserObject(User $user, $key)
    {
        return $this->deleteObject($this->getUserKey($user, $key));
    }

    private function getUserKey(User $user, $key)
    {
        return $user->getId() . '.' . $key;
    }
}