<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 14/01/2014
 * Time: 16:53
 */

namespace AdEntify\CoreBundle\Services;


use AdEntify\CoreBundle\Entity\Action;
use AdEntify\CoreBundle\Entity\Notification;
use AdEntify\CoreBundle\Entity\Tag;
use AdEntify\CoreBundle\Entity\User;
use AdEntify\CoreBundle\Entity\TagPoint;
use Doctrine\ORM\EntityManager;

class PointService
{
    const PHOTO_OWNER_POINTS = 5;

    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Set tag points with it tag type
     *
     * @param Tag $tag
     */
    public function calculateTagPoints(User $user, Tag $tag)
    {
        $points = 0;

        switch($tag->getType()) {
            case Tag::TYPE_BRAND:
            case Tag::TYPE_PRODUCT:
                $points = $tag->getDescription() ? TagPoint::TAG_POINTS_COMPLETE_PRODUCT : TagPoint::TAG_POINTS_PRODUCT;
                break;
            case Tag::TYPE_PERSON:
                $points = TagPoint::TAG_POINTS_PERSON;
                break;
            case Tag::TYPE_PLACE:
                $points = TagPoint::TAG_POINTS_PLACE;
                break;
        }

        $taggerIsPhotoOwner = $user->getId() == $tag->getPhoto()->getOwner()->getId();
        if ($taggerIsPhotoOwner) {
            $tagPoint = new TagPoint();
            $tag->addPoint($tagPoint);
            $tagPoint->setUser($user)->setPoints($points)->setType(TagPoint::TYPE_TAG_AND_PHOTO_OWNER);
            $this->em->persist($tagPoint);
        } else {
            // Photo owner points
            $tagPointPhotoOwner = new TagPoint();
            $tag->addPoint($tagPointPhotoOwner);
            $tagPointPhotoOwner->setUser($user)->setPoints(self::PHOTO_OWNER_POINTS)->setType(TagPoint::TYPE_PHOTO_OWNER);
            $this->em->persist($tagPointPhotoOwner);

            // Tag owner points
            $tagPointOwner = new TagPoint();
            $tag->addPoint($tagPointOwner);
            $tagPointOwner->setUser($tag->getOwner())->setPoints($points - self::PHOTO_OWNER_POINTS)->setType(TagPoint::TYPE_TAG_OWNER);
            $this->em->persist($tagPointOwner);
        }
    }

    /**
     * @param $user
     * @param Tag $tag
     */
    public function calculateUserPoints(User $user, Tag $tag)
    {
        $wonPoints = array();

        $taggerIsPhotoOwner = $user->getId() == $tag->getPhoto()->getOwner()->getId();
        if ($taggerIsPhotoOwner) {
            $points = $this->getPoints($tag, $taggerIsPhotoOwner);
            $user->setPoints($user->getPoints() + $points);

            $this->em->getRepository('AdEntifyCoreBundle:Action')->createAction(Action::TYPE_USER_POINTS,
                $user, $user, array($tag->getPhoto()), Action::getVisibilityWithPhotoVisibility($tag->getPhoto()->getVisibilityScope()), $tag->getPhoto()->getId(),
                $this->em->getClassMetadata(get_class($tag->getPhoto()))->getName(), true, 'tagPoints', array('count' => $points));

            $wonPoints['tagPointOwner'] = $points;
        } else if ($tag->getValidationStatus() == Tag::VALIDATION_GRANTED) {
            $result = $this->getPoints($tag, false);

            $tag->getPhoto()->getOwner()->setPoints($tag->getPhoto()->getOwner()->getPoints() + $result['tagPointOwner']->getPoints());
            $user->setPoints($user->getPoints() + $result['tagPointTagger']->getPoints());

            $this->em->getRepository('AdEntifyCoreBundle:Action')->createAction(Action::TYPE_USER_POINTS,
                $user, $user, array($tag->getPhoto()), Action::getVisibilityWithPhotoVisibility($tag->getPhoto()->getVisibilityScope()), $tag->getPhoto()->getId(),
                $this->em->getClassMetadata(get_class($tag->getPhoto()))->getName(), true, 'publicTaggerTagPoints', array('count' => $result['tagPointTagger']->getPoints()));

            $this->em->getRepository('AdEntifyCoreBundle:Action')->createAction(Action::TYPE_USER_POINTS,
                $tag->getPhoto()->getOwner(), $tag->getPhoto()->getOwner(), array($tag->getPhoto()), Action::getVisibilityWithPhotoVisibility($tag->getPhoto()->getVisibilityScope()), $tag->getPhoto()->getId(),
                $this->em->getClassMetadata(get_class($tag->getPhoto()))->getName(), true, 'publicTagPoints', array('count' => $result['tagPointOwner']->getPoints()));

            $wonPoints = $result;
        }

        foreach ($tag->getPoints() as $tagPoint) {
            $tag->setTotalPoints($tag->getTotalPoints() + $tagPoint->getPoints());
            $tagPoint->setCreditedAt(new \DateTime())->setStatus(TagPoint::STATUS_CREDITED);
            $this->em->merge($tagPoint);
        }

        $tag->getPhoto()->setTotalTagsPoints($tag->getPhoto()->getTotalTagsPoints() + $tag->getTotalPoints());
        $this->em->merge($tag->getPhoto());

        $this->em->merge($user);

        return $wonPoints;
    }

    /**
     * Remove user points on tag removing
     *
     * @param User $user
     * @param Tag $tag
     */
    public function removeUserPoints(User $user, Tag $tag)
    {
        $taggerIsPhotoOwner = $user->getId() == $tag->getPhoto()->getOwner()->getId();
        if ($taggerIsPhotoOwner) {
            $user->setPoints($user->getPoints() - $this->getPoints($tag, $taggerIsPhotoOwner));

            $this->em->getRepository('AdEntifyCoreBundle:Action')->removeAction(Action::TYPE_USER_POINTS, $user, $user,
                $tag->getPhoto()->getId(), $this->em->getClassMetadata(get_class($tag->getPhoto()))->getName());
        } else if ($tag->getValidationStatus() == Tag::VALIDATION_GRANTED) {
            $result = $this->getPoints($tag, false);

            $tag->getPhoto()->getOwner()->setPoints($tag->getPhoto()->getOwner()->getPoints() - $result['tagPointOwner']->getPoints());
            $user->setPoints($user->getPoints() - $result['tagPointTagger']->getPoints());

            $this->em->getRepository('AdEntifyCoreBundle:Action')->removeAction(Action::TYPE_USER_POINTS, $user, $user,
                $tag->getPhoto()->getId(), $this->em->getClassMetadata(get_class($tag->getPhoto()))->getName());

            $this->em->getRepository('AdEntifyCoreBundle:Action')->removeAction(Action::TYPE_USER_POINTS, $user,
                $tag->getPhoto()->getOwner(), $tag->getPhoto()->getId(), $this->em->getClassMetadata(get_class($tag->getPhoto()))->getName());
        }

        foreach ($tag->getPoints() as $tagPoint) {
            $this->em->remove($tagPoint);
        }

        $tag->getPhoto()->setTotalTagsPoints($tag->getPhoto()->getTotalTagsPoints() - $tag->getTotalPoints());
        $this->em->merge($tag->getPhoto());

        $this->em->merge($user);
    }

    /**
     * Get points for the tag
     *
     * @param $tag
     * @param $isPhotoOwner
     * @return array|int
     */
    private function getPoints($tag, $isPhotoOwner)
    {
        if ($isPhotoOwner) {
            $points = 0;
            foreach($tag->getPoints() as $tagPoint) {
                if ($tagPoint->getType() == TagPoint::TYPE_TAG_AND_PHOTO_OWNER) {
                    $points = $tagPoint->getPoints();
                    break;
                }
            }
            return $points;
        } else {
            $tagPointTagger = null;
            $tagPointOwner = null;
            foreach ($tag->getPoints() as $tagPoint) {
                if ($tagPoint->getType() == TagPoint::TYPE_TAG_OWNER)
                    $tagPointTagger = $tagPoint;
                else if($tagPoint->getType() == TagPoint::TYPE_PHOTO_OWNER)
                    $tagPointOwner = $tagPoint;
            }
            return array(
                'tagPointTagger' => $tagPointTagger,
                'tagPointOwner' => $tagPointOwner
            );
        }
    }
} 