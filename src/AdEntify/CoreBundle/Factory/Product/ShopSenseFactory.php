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
    const API_BASE_URL = 'http://api.shopstyle.com/api/v2/';

    protected $productProvider = null;

    public function __construct($em, Client $client)
    {
        parent::__construct($em, $client);
        $this->productProvider = $em->getRepository('AdEntifyCoreBundle:ProductProvider')->findOneBy(array('providerKey' => Factory::FACTORY_SHOPSENSE));
    }

    public function search(&$products, $options = array())
    {
        if (!array_key_exists('limit', $options))
            $options['limit'] = 20;
        $options['url'] = sprintf('%sproducts?pid=uid321-26129111-96&fts=%s&limit=%s', self::API_BASE_URL, $options['keywords'], $options['limit']);
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

    public function getProductById($id, $options = array())
    {
        $options['url'] = sprintf('%s/products/%s?pid=uid321-26129111-96', self::API_BASE_URL, $id);
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

    public function getBrandById($id, $options = array())
    {
        $options['url'] = sprintf('%s/brands/%s?pid=uid321-26129111-96', self::API_BASE_URL, $id);
        $request = parent::getBrandId($id, $options);
        $request = $this->client->send($request);
        if ($request && $request->getStatusCode() == 200) {
            $body = json_decode($request->getBody());
            return $this->buildBrand(array(
                'json' => $body
            ));
        }

        return null;
    }

    public function getRetailerById($id, $options = array())
    {
        $options['url'] = sprintf('%s/retailers/%s?pid=uid321-26129111-96', self::API_BASE_URL, $id);
        $request = parent::getRetailerById($id, $options);
        $request = $this->client->send($request);
        if ($request && $request->getStatusCode() == 200) {
            $body = json_decode($request->getBody());
            return $this->buildRetailer(array(
                'json' => $body
            ));
        }

        return null;
    }

    public function buildProduct($options = array())
    {
        $product = parent::buildProduct($options);

        if (array_key_exists('json', $options)) {
            $data = $options['json'];
            if (!array_key_exists('retailer', $options) || $options['retailer'] !== false) {
                $retailer = $this->loadRetailerFromJSON($data);
                if ($retailer)
                    $product->setProductRetailer($retailer);
            }
            if (!array_key_exists('brand', $options) || $options['brand'] !== false) {
                $brand = $this->loadBrandFromJSON($data);
                if ($brand) {
                    $product->setBrand($brand);
                    $brand->setProductProvider($this->productProvider);
                }
            }
            $this->loadProductFromJSON($product, $data);
        }

        $product->setProductProvider($this->productProvider);

        return $product;
    }

    public function buildBrand($options = array())
    {
        $brand = parent::buildBrand($options);

        if (array_key_exists('json', $options)) {
            $data = $options['json'];
            $this->loadBrandFromJSON($brand, $data);
        }

        $brand->setProductProvider($this->productProvider);

        return $brand;
    }

    /**
     * Load retailer
     *
     * @param $product
     * @param $json
     */
    protected function loadRetailerFromJSON($json, $persist = false)
    {
        if (property_exists($json, 'retailer') || property_exists($json, 'id')) {
            $json = property_exists($json, 'retailer') ? $json->retailer : $json;
            $retailer = $this->em->getRepository('AdEntifyCoreBundle:ProductRetailer')->findOneBy(array(
                'providerId' => $json->id
            ));
            if (!$retailer) {
                // Create new retailer
                $retailer = new ProductRetailer();
                $retailer->setProviderId($json->id)
                    ->setName($json->name)
                    ->setDeeplinkSupport(array_key_exists('deeplinkSupport', $json) ? $json->deeplinkSupport : false);

                if (property_exists($json, 'hostDomain'))
                    $retailer->setHostDomain($json->hostDomain);
                if (property_exists($json, 'mobileOptimized'))
                    $retailer->setMobileOptimized($json->mobileOptimized);
                if (property_exists($json, 'logo'))
                    $retailer->setLogo($json->logo);

                if ($persist)
                    $this->em->persist($retailer);
            }

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
    protected function loadBrandFromJSON($json, $persist = false)
    {
        if (property_exists($json, 'brand') || property_exists($json, 'id')) {
            $json = property_exists($json, 'brand') ? $json->brand : $json;
            $brand = $this->em->getRepository('AdEntifyCoreBundle:Brand')->findOneByProviderIdOrName($json->id, $json->name);
            if (!$brand) {
                // Create new brand
                $brand = new Brand();
                $brand->setProviderId($json->id)
                    ->setName($json->name);

                if ($persist)
                    $this->em->persist($brand);
            }

            return $brand;
        }

        return null;
    }

    /**
     * Load product
     *
     * @param $json
     */
    protected function loadProductFromJSON(Product $product, $json)
    {
        $product->setName($json->name)->setProductProviderId($json->id)->setId(0);

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