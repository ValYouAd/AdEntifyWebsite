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
use Guzzle\Http\Client;

class ShopSenseFactory extends BaseProductFactory
{
    const API_BASE_URL = 'http://api.shopstyle.com/api/v2/products';

    protected $productProvider = null;

    public function __construct($em, Client $client)
    {
        parent::__construct($em, $client);
        $this->productProvider = $em->getRepository('AdEntifyCoreBundle:ProductProvider')->findOneBy(array('providerKey' => Factory::FACTORY_SHOPSENSE));
    }

    public function search(&$products, $options = array())
    {
        $options['url'] = sprintf('%s?pid=uid321-26129111-96&fts=%s', self::API_BASE_URL, $options['keywords']);
        $request = parent::search($products, $options);
        $request->getEventDispatcher()->addListener('request.success', function ($e) use (&$products) {
            if ($e['response']->getStatusCode() == 200) {
                $body = json_decode($e['response']->getBody());
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
            $data = $options['json'];
            $this->loadRetailerFromJSON($product, $data);
            $this->loadBrandFromJSON($product, $data);
            $this->loadProductFromJSON($product, $data);
        }

        $product->setProductProvider($this->productProvider);

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
                'providerId' => $json->retailer->id
            ));
            if (!$retailer) {
                // Create new retailer
                $retailer = new ProductRetailer();
                $retailer->setProviderId($json->retailer->id)
                    ->setName($json->retailer->name)
                    ->setDeeplinkSupport(array_key_exists('deeplinkSupport', $json->retailer) ? $json->retailer->deeplinkSupport : false);

                if (property_exists($json->retailer, 'hostDomain'))
                    $retailer->setHostDomain($json->retailer->hostDomain);
                if (property_exists($json->retailer, 'mobileOptimized'))
                    $retailer->setMobileOptimized($json->retailer->mobileOptimized);
                if (property_exists($json->retailer, 'logo'))
                    $retailer->setLogo($json->retailer->logo);

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
            $brand = $this->em->getRepository('AdEntifyCoreBundle:Brand')->findOneByProviderIdOrName($json->brand->id, $json->brand->name);
            if (!$brand) {
                // Create new brand
                $brand = new Brand();
                $brand->setProviderId($json->brand->id)
                    ->setName($json->brand->name);

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
        if (property_exists($json, 'image') && property_exists($json->image, 'sizes')) {
            $sizes = $json->image->sizes;
            if (property_exists($sizes, 'Medium'))
                $product->setMediumUrl($sizes->Medium->url);
            if (property_exists($sizes, 'Original'))
                $product->setOriginalUrl($sizes->Original->url);
            if (property_exists($sizes, 'Small'))
                $product->setSmallUrl($sizes->Small->url);
        }

        return $product;
    }
} 