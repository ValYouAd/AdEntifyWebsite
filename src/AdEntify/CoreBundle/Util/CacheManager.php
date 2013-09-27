<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 26/09/2013
 * Time: 15:41
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Util;

class CacheManager
{
    protected static $instance = null;
    protected function __construct()
    {
        //Thou shalt not construct that which is unconstructable!
    }

    protected function __clone()
    {
        //Me not like clones! Me smash clones!
    }

    /**
     * @return CacheManager
     */
    public static function getInstance()
    {
        if (!isset(static::$instance)) {
            static::$instance = new static;
        }
        return static::$instance;
    }

    /**
     * Get an object from the cache
     *
     * @param $key
     * @return mixed
     */
    public function getObject($key)
    {
        $value = apc_fetch($key, $success);
        return $success === true ? $value : null;

    }

    /**
     * Set an object to the cache
     *
     * @param $key
     * @param $value
     * @param int $ttl
     * @return array|bool
     */
    public function setObject($key, $value, $ttl = 0)
    {
        return apc_store($key, $value, $ttl);
    }

    /**
     * Delete object from the cache
     *
     * @param $key
     */
    public function deleteObject($key)
    {
        return apc_delete($key);
    }
}