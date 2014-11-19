<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 19/11/14
 * Time: 11:11
 */

namespace AdEntify\CoreBundle\Factory\Product;

use AdEntify\CoreBundle\Entity\Product;
use Guzzle\Http\Client;

abstract class BaseProductFactory
{
    protected $em;
    protected $client;

    public function __construct($em, Client $client)
    {
        $this->em = $em;
        $this->client = $client;
    }

    public function search($products, $options = array())
    {
        // Create a search request to the API
        return $this->client->createRequest('GET', $options['url']);
    }

    public function build($options = array())
    {
        return new Product();
    }
} 