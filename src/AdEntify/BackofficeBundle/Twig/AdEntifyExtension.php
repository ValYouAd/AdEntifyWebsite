<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 23/10/2013
 * Time: 14:47
 */

namespace AdEntify\BackofficeBundle\Twig;


class AdEntifyExtension extends \Twig_Extension
{
    protected $container;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return array(
            'ceil' => new \Twig_Filter_Method($this, 'ceil'),
            'current' => new \Twig_Filter_Method($this, 'isCurrent'),
        );
    }

    public function isCurrent($routePrefix)
    {
        $request = $this->container->get('request');
        $routeName = $request->get('_route');

        return preg_match('#^' . $routePrefix . '#', $routeName) ? 'active' : '';
    }

    public function ceil($number)
    {
        return ceil($number);
    }

    public function getName()
    {
        return 'adentify_extension';
    }
}