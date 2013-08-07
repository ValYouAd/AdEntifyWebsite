<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 06/08/2013
 * Time: 16:59
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Services;

use AdEntify\CoreBundle\Entity\BrandTag;
use AdEntify\CoreBundle\Entity\Tag;
use AdEntify\CoreBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class TagRevenueService
{
    protected $em;

    public function __construct($em) {
        $this->em = $em;
    }

    /**
     * @param Tag $tag
     * @param User $user
     * @param Request $request
     */
    public function calculateRevenueForBrandTagging(Tag $tag, Request $request)
    {
        if (!$tag->getWaitingValidation() && $tag->getProduct() && $tag->getProduct()->getBrand() && !$tag->getRevenueAssigned())
        {
            $brand = $tag->getProduct()->getBrand();
            $brandTag = new BrandTag();
            $brandTag->setUser($tag->getOwner())->setBrand($brand)->setTag($tag)->setPhoto($tag->getPhoto())
                ->setIpAddress($request->getClientIp())->setIncome($brand->getCostPerTag());

            $this->em->persist($brandTag);

            $tag->setRevenueAssigned(true);
            $this->em->merge($tag);
            $this->em->flush();
        }
    }
}