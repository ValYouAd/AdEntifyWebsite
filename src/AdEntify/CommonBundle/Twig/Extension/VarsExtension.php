<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 20/05/2014
 * Time: 12:44
 */

namespace AdEntify\CommonBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;
use \Twig_Extension;

class VarsExtension  extends Twig_Extension
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'adentify_varsextension';
    }

    public function getFilters() {
        return array(
            'json_decode'   => new \Twig_Filter_Method($this, 'jsonDecode'),
        );
    }

    public function jsonDecode($json) {
        return json_decode($json);
    }
}