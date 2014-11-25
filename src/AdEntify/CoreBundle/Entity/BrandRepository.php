<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 10/10/2013
 * Time: 13:49
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class BrandRepository extends EntityRepository
{
    /**
     * Create or Update a brand from a Facebook Graph Object
     *
     * @param $facebookId
     * @param \BaseFacebook $fbApi
     * @return Brand|bool|object
     */
    public function createOrUpdateBrandFromFacebookId($facebookId, \BaseFacebook $fbApi)
    {
        try {
            // Call Graph API to get object details
            $result = $fbApi->api($facebookId);
            if ($result) {
                // Check if its a page
                if (isset($result['first_name'])) {
                    return false;
                }

                $brand = $this->findOneBy(array(
                    'name' => $result['name']
                ));
                if ($brand) {
                    $updated = false;
                    if (!$brand->getFacebookUrl()) {
                        $brand->setFacebookUrl($result['link']);
                        $updated = true;
                    }
                    if (!$brand->getDescription()) {
                        if (array_key_exists('company_overview', $result)) {
                            $brand->setDescription($result['company_overview']);
                            $updated = true;
                        } else if (array_key_exists('about', $result)) {
                            $brand->setDescription($result['about']);
                            $updated = true;
                        }
                    }
                    if (array_key_exists('website', $result) && !$brand->getWebsiteUrl()) {
                        $brand->setWebsiteUrl($result['website']);
                        $updated = true;
                    }

                    if ($updated) {
                        $this->getEntityManager()->merge($brand);
                        $this->getEntityManager()->flush();
                    }

                    return $brand;
                }
                else {
                    $brand = new Brand();
                    $brand->setCostPerTag(0)->setFacebookUrl($result['link'])->setName($result['name'])
                        ->setLargeLogoUrl('https://graph.facebook.com/'.$result['id'].'/picture?width=500')
                        ->setMediumLogoUrl('https://graph.facebook.com/'.$result['id'].'/picture?width=250')
                        ->setSmallLogoUrl('https://graph.facebook.com/'.$result['id'].'/picture?width=100')
                        ->setOriginalLogoUrl('https://graph.facebook.com/'.$result['id'].'/picture?width=1000');

                    if (array_key_exists('company_overview', $result)) {
                        $brand->setDescription($result['company_overview']);
                    } else if (array_key_exists('about', $result)) {
                        $brand->setDescription($result['about']);
                    }
                    if (array_key_exists('website', $result)) {
                        $brand->setWebsiteUrl($result['website']);
                    }

                    $this->getEntityManager()->persist($brand);
                    $this->getEntityManager()->flush();
                    return $brand;
                }
            } else {
                return false;
            }
        } catch (\FacebookApiException $e) {
            return false;
        }
    }

    public function findOneByProviderIdOrName($providerId, $name)
    {
        return $this->createQueryBuilder('b')
            ->where('b.name = :name')
            ->orWhere('b.providerId = :providerId')
            ->setParameters(array(
                'name' => $name,
                'providerId' => $providerId
            ))->getQuery()->getOneOrNullResult();
    }
}