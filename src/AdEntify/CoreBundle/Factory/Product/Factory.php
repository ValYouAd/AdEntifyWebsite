<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 19/11/14
 * Time: 11:37
 */

namespace AdEntify\CoreBundle\Factory\Product;

class Factory
{
    const FACTORY_SHOPSENSE = 'shopsense';

    public function getProduct($factoryName, $options)
    {
        switch ($factoryName)
        {
            case self::FACTORY_SHOPSENSE:
                return (new ShopSenseFactory())->build($options);
            default:
                throw new \Exception('Factory not supported');
        }
    }
} 