<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 19/11/14
 * Time: 11:13
 */

namespace AdEntify\CoreBundle\Factory\Product;

use AdEntify\CoreBundle\Entity\Brand;
use AdEntify\CoreBundle\Entity\Product;
use AdEntify\CoreBundle\Entity\ProductRetailer;
use Guzzle;

class ShopSenseFactory extends BaseProductFactory
{
    const API_BASE_URL = 'http://www.shopstyle.com/browse/';

    public function search($products, $options = array())
    {
        $options['url'] = self::API_BASE_URL . $options['query'];
        $request = parent::search($products, $options);

        $request->getEventDispatcher()->addListener('request.success', function ($e) {
            if ($e['response']) {
                $body = $e['response'];
                if (property_exists($body, 'products') && is_array($body->products) && count($body->products)) {
                    foreach ($body->products as $product) {
                        $product = $this->build(array(
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

    public function build($options = array())
    {
        $product = parent::build($options);

        if (array_key_exists('json', $options)) {
            $data = json_decode($options['json']);
            $this->loadRetailerFromJSON($product, $data);
            $this->loadBrandFromJSON($product, $data);
            $this->loadBrandFromJSON($product, $data);
        }

        $product->setProviderName(Factory::FACTORY_SHOPSENSE);

        return $product;
    }

    /**
     * Load retailer
     *
     * @param $product
     * @param $json
     */
    protected function loadRetailerFromJSON($product, $json, $persist = false)
    {
        if (property_exists($json, 'retailer')) {
            $retailer = $this->em->getRepository('AdEntifyCoreBundle:ProductRetailer')->findOneBy(array(
                'providerId' => $json->retailer['id']
            ));
            if (!$retailer) {
                // Create new retailer
                $retailer = new ProductRetailer();
                $retailer->setProviderId($json->retailer['id'])
                    ->setName($json->retailer['name'])
                    ->setDeeplinkSupport(array_key_exists('deeplinkSupport', $json->retailer) ? $json->retailer['deeplinkSupport'] : false);

                if (array_key_exists('hostDomain', $json->retailer))
                    $retailer->setHostDomain($json->retailer['hostDomain']);
                if (array_key_exists('mobileOptimized', $json->retailer))
                    $retailer->setMobileOptimized($json->retailer['mobileOptimized']);
                if (array_key_exists('logo', $json->retailer))
                    $retailer->setLogo($json->retailer['logo']);

                if ($persist)
                    $this->em->persist($retailer);
            }

            $product->setProductRetailer($retailer);

            return $retailer;
        }

        return null;
    }

    /**
     * Load brand
     *
     * @param $product
     * @param $json
     * @return
     */
    protected function loadBrandFromJSON($product, $json, $persist = false)
    {
        if (property_exists($json, 'brand')) {
            $brand = $this->em->getRepository('AdEntifyCoreBundle:Brand')->findOneByProviderIdOrName($json->brand['id'], $json->brand['name']);
            if (!$brand) {
                // Create new brand
                $brand = new Brand();
                $brand->setProviderId($json->brand['id'])
                    ->setName($json->brand['name']);

                if ($persist)
                    $this->em->persist($brand);
            }

            $product->setBrand($brand);

            return $brand;
        }

        return null;
    }

    /**
     * Load product
     *
     * @param $json
     */
    protected function loadProductFromJSON($product, $json)
    {
        $product->setName($json->name);

        if (property_exists($json, 'description'))
            $product->setDescription($json->description);
        if (property_exists($json, 'clickUrl'))
            $product->setPurchaseUrl($json->clickUrl);
        if (property_exists($json, 'currency'))
            $product->setCurrency($json->currency);
        if (property_exists($json, 'price'))
            $product->setPrice($json->price);

        return $product;
    }
} 