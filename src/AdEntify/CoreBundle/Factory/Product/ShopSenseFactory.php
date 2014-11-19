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

class ShopSenseFactory extends BaseProductFactory
{
    public function build($options)
    {
        parent::build($options);

        if (array_key_exists('json', $options)) {
            $data = json_decode($options['json']);
            $this->loadRetailer($data);
            $this->loadBrand($data);
            $this->loadBrand($data);
        }

        $this->getProduct()->setProviderName(Factory::FACTORY_SHOPSENSE);

        return $this->getProduct();
    }

    /**
     * Load retailer
     *
     * @param $json
     */
    protected function loadRetailer($json)
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

                $this->em->persist($retailer);
            }

            $this->getProduct()->setProductRetailer($retailer);

            return $retailer;
        }

        return null;
    }

    /**
     * Load brand
     *
     * @param $json
     */
    protected function loadBrand($json)
    {
        if (property_exists($json, 'brand')) {
            $brand = $this->em->getRepository('AdEntifyCoreBundle:Brand')->findOneByProviderIdOrName($json->brand['id'], $json->brand['name']);
            if (!$brand) {
                // Create new brand
                $brand = new Brand();
                $brand->setProviderId($json->brand['id'])
                    ->setName($json->brand['name']);

                $this->em->persist($brand);
            }

            $this->getProduct()->setBrand($brand);

            return $brand;
        }

        return null;
    }

    /**
     * Load product
     *
     * @param $json
     */
    protected function loadProduct($json)
    {
        $this->getProduct()->setName($json->name);

        if (property_exists($json, 'description'))
            $this->getProduct()->setDescription($json->description);
        if (property_exists($json, 'clickUrl'))
            $this->getProduct()->setPurchaseUrl($json->clickUrl);
        if (property_exists($json, 'currency'))
            $this->getProduct()->setCurrency($json->currency);
        if (property_exists($json, 'price'))
            $this->getProduct()->setPrice($json->price);

        return $this->getProduct();
    }
} 