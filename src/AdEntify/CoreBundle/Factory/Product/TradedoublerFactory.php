<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 */

namespace AdEntify\CoreBundle\Factory\Product;

use AdEntify\CoreBundle\Entity\Brand;
use AdEntify\CoreBundle\Entity\Product;
use AdEntify\CoreBundle\Entity\ProductRetailer;
use Guzzle;
use Guzzle\Http\Client;

class TradedoublerFactory extends BaseProductFactory
{
    const API_BASE_URL = 'http://api.tradedoubler.com/1.0/';

    protected $productProvider = null;

    public function __construct($em, Client $client)
    {
        parent::__construct($em, $client);
        $this->productProvider = $em->getRepository('AdEntifyCoreBundle:ProductProvider')->findOneBy(array('providerKey' => Factory::FACTORY_TRADEDOUBLER));
    }

    public function search(&$products, $options = array())
    {
        if (!array_key_exists('limit', $options))
            $options['limit'] = 20;
        $options['url'] = sprintf('%sproducts.json;q=%s;limit=%s?token=C7A6CBBEC36515B97737191DE48A80DA35A50067', self::API_BASE_URL, $options['keywords'], $options['limit']);
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

    /**
     * Get product by sku identifier
     * @param  integer $id      SKU
     * @param  array  $options [description]
     * @return Product          AdEntify Product entity
     */
    public function getProductById($id, $options = array())
    {
        $options['url'] = sprintf('%sproducts.json;sku=%s;limit=1?token=C7A6CBBEC36515B97737191DE48A80DA35A50067', self::API_BASE_URL, $id);
        $request = parent::getProductById($id, $options);
        $request = $this->client->send($request);
        if ($request && $request->getStatusCode() == 200) {
            $body = json_decode($request->getBody());
            return $this->buildProduct(array_merge(array(
                'json' => $body
            ), $options));
        }

        return null;
    }

    public function buildProduct($options = array())
    {
        $product = parent::buildProduct($options);

        if (array_key_exists('json', $options)) {
            $data = $options['json'];
            $this->loadProductFromJSON($product, $data);
        }

        $product->setProductProvider($this->productProvider);

        return $product;
    }

    /**
     * Load product
     *
     * @param $json
     */
    protected function loadProductFromJSON(Product $product, $json)
    {
        $product
            ->setName($json->name)
            ->setProductProviderId($json->identifiers->sku)
            ->setId(0);

        if (property_exists($json, 'description'))
            $product->setDescription($json->description);

        if (property_exists($json, 'offers') && is_array($json->offers) && 0 < count($json->offers)) {
            // Get last offer
            $offer = end($json->offers);
            if (property_exists($offer, 'productUrl'))
                $product->setPurchaseUrl($offer->productUrl);

            // Get last price (with currency)
            if (property_exists($offer, 'priceHistory')) {
                $price = end($offer->priceHistory);
                $product->setPrice($price->price->value);
                $product->setCurrency($price->price->currency);
            }
        }

        if (property_exists($json, 'productImage'))
            $product->setOriginalUrl($json->productImage->url);

        return $product;
    }
} 