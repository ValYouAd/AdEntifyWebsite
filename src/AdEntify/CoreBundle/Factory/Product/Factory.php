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
    const FACTORY_EFFINITY = 'effinity';

    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getProductFactory($factoryName)
    {
        switch ($factoryName)
        {
            case self::FACTORY_SHOPSENSE:
                return $this->container->get('ad_entify_core.shopSenseFactory');
            case self::FACTORY_EFFINITY:
                return $this->container->get('ad_entify_core.effinityFactory');
            default:
                throw new \Exception('Factory not supported');
        }
    }
} 