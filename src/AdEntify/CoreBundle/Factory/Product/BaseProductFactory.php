<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 19/11/14
 * Time: 11:11
 */

namespace AdEntify\CoreBundle\Factory\Product;

use AdEntify\CoreBundle\Entity\Product;

abstract class BaseProductFactory
{
    protected $product;
    protected $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    public function build($options)
    {
        $this->setProduct(new Product());
        return $this->getProduct();
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
        return $this;
    }
} 