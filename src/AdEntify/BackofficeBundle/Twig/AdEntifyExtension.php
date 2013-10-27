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
    public function getFilters()
    {
        return array(
            'ceil' => new \Twig_Filter_Method($this, 'ceil'),
        );
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