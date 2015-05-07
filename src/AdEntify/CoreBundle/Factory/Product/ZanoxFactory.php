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

class ZanoxFactory extends BaseProductFactory
{
    const API_BASE_URL = 'http://api.zanox.com/json/2011-03-01/';

    protected $productProvider = null;

    public function __construct($em, Client $client)
    {
        parent::__construct($em, $client);
        $this->productProvider = $em->getRepository('AdEntifyCoreBundle:ProductProvider')->findOneBy(array('providerKey' => Factory::FACTORY_ZANOX));
    }

    public function search(&$products, $options = array())
    {   
        if (!array_key_exists('limit', $options))
            $options['limit'] = 20;
        $options['url'] = sprintf('%sproducts?connectid=EEFF059400699C7AE7DE&q=%s&limit=%s', self::API_BASE_URL, $options['keywords'], $options['limit']);
        $request = parent::search($products, $options);
        $request->getEventDispatcher()->addListener('request.success', function ($e) use (&$products) {
            if ($e['response']->getStatusCode() == 200) {
                $body = json_decode($e['response']->getBody());
                if (property_exists($body, 'productItems') && is_array($body->productItems->productItem) && count($body->productItems->productItem)) {
                    foreach ($body->productItems->productItem as $product) {
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
        $options['url'] = sprintf('%sproducts/product/%s?connectid=EEFF059400699C7AE7DE', self::API_BASE_URL, $id);
        $request = parent::getProductById($id, $options);
        $request = $this->client->send($request);
        if ($request && $request->getStatusCode() == 200) {
            $body = json_decode($request->getBody());
            if (property_exists($body, 'productItem') && count($body->productItem))
                return $this->buildProduct(array_merge(array(
                    'json' => $body->productItem[0]
                ), $options));
        }

        return null;
    }

    public function buildProduct($options = array())
    {
        $product = parent::buildProduct($options);

        if (array_key_exists('json', $options)) {
            $data = $options['json'];

            // Load brand
            $brand = $this->loadBrandFromJSON($data);
            if ($brand) {
                $product->setBrand($brand);
                $brand->setProductProvider($this->productProvider);
            }

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
        $product->setName($json->name)->setProductProviderId($json->{"@id"})->setId(0);

        if (property_exists($json, 'descriptionLong'))
            $product->setDescription($json->descriptionLong);
        elseif (property_exists($json, 'description'))
            $product->setDescription($json->description);
        if (property_exists($json, 'trackingLinks') && property_exists($json->trackingLinks, 'trackingLink') 
            && is_array($json->trackingLinks->trackingLink))
            $product->setPurchaseUrl($json->trackingLinks->trackingLink[0]->ppc);
        if (property_exists($json, 'currency'))
            $product->setCurrency($json->currency);
        if (property_exists($json, 'price'))
            $product->setPrice($json->price);
        if (property_exists($json, 'image')) {
            if (property_exists($json->image, 'medium'))
                $product->setMediumUrl($sizes->medium);
            if (property_exists($json->image, 'original'))
                $product->setOriginalUrl($json->image->original);
            if (property_exists($json->image, 'large'))
                $product->setLargeUrl($json->image->large);
            if (property_exists($json->image, 'small'))
                $product->setSmallUrl($json->image->small);
        }

        return $product;
    }

    /**
     * Load brand
     *
     * @param $json
     * @param $persist
     * @return
     */
    protected function loadBrandFromJSON($json, $persist = false)
    {
        if (property_exists($json, 'manufacturer')) {
            $brand = $this->em->getRepository('AdEntifyCoreBundle:Brand')->findOneByName($json->manufacturer);
            if (!$brand) {
                // Create new brand
                $brand = new Brand();
                $brand->setName($json->manufacturer);

                if ($persist)
                    $this->em->persist($brand);
            }

            return $brand;
        }

        return null;
    }
} 