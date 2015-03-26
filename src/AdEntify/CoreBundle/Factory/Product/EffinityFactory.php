<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 26/03/15
 * Time: 16:43
 */

namespace AdEntify\CoreBundle\Factory\Product;

use AdEntify\CoreBundle\Entity\Brand;
use AdEntify\CoreBundle\Entity\Product;
use AdEntify\CoreBundle\Entity\ProductRetailer;
use Guzzle;
use Guzzle\Http\Client;

class EffinityFactory extends BaseProductFactory
{
    const API_BASE_URL = 'http://apiv2.effiliation.com/apiv2/';

    protected $productProvider = null;

    public function __construct($em, Client $client)
    {
        parent::__construct($em, $client);
        $this->productProvider = $em->getRepository('AdEntifyCoreBundle:ProductProvider')->findOneBy(array('providerKey' => Factory::FACTORY_SHOPSENSE));
    }

    public function search(&$products, $options = array())
    {
        $options = $this->setDefaultOptions($options);

        $options['url'] = sprintf('%sproductfeeds.json?key=%s&fts=%s&limit=%s', self::API_BASE_URL, $options['api_key'], $options['keywords'], $options['limit']);

        $request = parent::search($products, $options);
        $request->getEventDispatcher()->addListener('request.success', function ($e) use (&$products) {
            if ($e['response']->getStatusCode() == 200) {
                $body = json_decode($e['response']->getBody());
                if (property_exists($body, 'products') && is_array($body->products) && count($body->products)) {
                    foreach ($body->products as $product) {
                        $product = $this->buildProduct(array(
                            'json' => $product
                        ));
                        if ($product)
                            $products[] = $product;
                    }
                }
            }
        });

        return $request;
    }

    private function setDefaultOptions(array $options)
    {
        if (!is_array($options))
            return $options;

        $defaultOptions = array(
            'api_key' => 'sze5tJyY1ZtM3VWGP9YA2tAgf27zHJbj',
            'limit' => 20
        );

        return array_merge($defaultOptions, $options);
    }
}