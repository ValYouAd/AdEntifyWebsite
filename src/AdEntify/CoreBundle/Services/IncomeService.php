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
use AdEntify\CoreBundle\Entity\TagIncome;
use AdEntify\CoreBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;

class IncomeService
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
    public function calculateIncome(Tag $tag, User $user, Request $request)
    {
        if (!$tag->getWaitingValidation() && !$tag->getRevenueAssigned())
        {
            $income = 0;
            // Calculate user income
            switch ($tag->getType()) {
                case Tag::TYPE_BRAND:
                case Tag::TYPE_PRODUCT:
                    if ($tag->getProduct() && $tag->getProduct()->getCostPerTag() > 0) {
                        $income = $tag->getProduct()->getCostPerTag() - ($tag->getProduct()->getCostPerTag() * ($tag->getProduct()->getAdentifyFees() / 100));
                    } else if ($tag->getBrand()->getCostPerTag() > 0) {
                        $income = $tag->getBrand()->getCostPerTag() - ($tag->getBrand()->getCostPerTag() * ($tag->getBrand()->getAdentifyFees() / 100));
                    }
                    break;
                case Tag::TYPE_PERSON:
                    if ($tag->getPerson()->getCostPerTag() > 0)
                        $income = $tag->getPerson()->getCostPerTag() - ($tag->getPerson()->getCostPerTag() * ($tag->getPerson()->getAdentifyFees() / 100));
                    break;
                case Tag::TYPE_PLACE:
		    if ($tag->getVenue() && $tag->getVenue()->getCostPerTag() > 0)
                        $income = $tag->getVenue()->getCostPerTag() - ($tag->getVenue()->getCostPerTag() * ($tag->getVenue()->getAdentifyFees() / 100));
                    break;
            }

            if ($income > 0) {
                // Check if tagger is photo owner
                if ($user->getId() == $tag->getPhoto()->getOwner()->getId()) {
                    $tagIncome = new TagIncome();
                    $tagIncome->setUser($tag->getOwner())->setStatus(TagIncome::STATUS_WAITING)->setTag($tag)
                        ->setIpAddress($request->getClientIp())->setUser($user)->setIncome($income);;
                    $this->em->persist($tagIncome);
                } else {
                    // If not, share income
                    $tagIncomeTagger = new TagIncome();
                    $tagIncomeTagger->setUser($tag->getOwner())->setStatus(TagIncome::STATUS_WAITING)->setTag($tag)
                        ->setIpAddress($request->getClientIp())->setUser($user)->setIncome($income / 2);;
                    $this->em->persist($tagIncomeTagger);

                    $tagIncomePhotoOwner = new TagIncome();
                    $tagIncomePhotoOwner->setUser($tag->getOwner())->setStatus(TagIncome::STATUS_WAITING)->setTag($tag)
                        ->setIpAddress($request->getClientIp())->setUser($tag->getPhoto()->getOwner())->setIncome($income / 2);;
                    $this->em->persist($tagIncomePhotoOwner);
                }
            }

            $tag->setRevenueAssigned(true);
            $this->em->merge($tag);
            $this->em->flush();
        }
    }
}