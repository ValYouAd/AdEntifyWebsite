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
use Doctrine\ORM\EntityManager;

class PointService
{
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
    public function calculateTagPoints(Tag $tag)
    {
        switch($tag->getType()) {
            case Tag::TYPE_BRAND:
            case TAG::TYPE_PRODUCT:
                $tag->setPoints($tag->getDescription() ? Tag::TAG_POINTS_COMPLETE_PRODUCT : Tag::TAG_POINTS_PRODUCT);
                break;
            case Tag::TYPE_PERSON:
                $tag->setPoints(Tag::TAG_POINTS_PERSON);
                break;
            case Tag::TYPE_PLACE:
                $tag->setPoints(Tag::TAG_POINTS_PLACE);
                break;
        }
    }

    /**
     * @param $user
     * @param Tag $tag
     */
    public function calculateUserPoints($user, Tag $tag)
    {
        $taggerIsPhotoOwner = $user->getId() == $tag->getPhoto()->getOwner()->getId();
        if ($taggerIsPhotoOwner) {
            $user->setPoints($user->getPoints() + $tag->getPoints());

            $this->em->getRepository('AdEntifyCoreBundle:Action')->createAction(Action::TYPE_USER_POINTS,
                $user, null, array($tag->getPhoto()), Action::getVisibilityWithPhotoVisibility($tag->getPhoto()->getVisibilityScope()), $tag->getPhoto()->getId(),
                get_class($tag->getPhoto()), true, 'tagPoints', array('count' => $tag->getPoints()));
        } else if ($tag->getValidationStatus() == Tag::VALIDATION_GRANTED) {
            $tag->getPhoto()->getOwner()->setPoints($tag->getPhoto()->getOwner() + 5);
            $user->setPoints($user->getPoints() + ($tag->getPoints() - 5));

            $this->em->getRepository('AdEntifyCoreBundle:Action')->createAction(Action::TYPE_USER_POINTS,
                $user, null, array($tag->getPhoto()), Action::getVisibilityWithPhotoVisibility($tag->getPhoto()->getVisibilityScope()), $tag->getPhoto()->getId(),
                get_class($tag->getPhoto()), true, 'tagPoints', array('count' => ($tag->getPoints() - 5)));

            $this->em->getRepository('AdEntifyCoreBundle:Action')->createAction(Action::TYPE_USER_POINTS,
                $user, $tag->getPhoto()->getOwner(), array($tag->getPhoto()), Action::getVisibilityWithPhotoVisibility($tag->getPhoto()->getVisibilityScope()), $tag->getPhoto()->getId(),
                get_class($tag->getPhoto()), true, 'tagPoints', array('count' => 5));
        }

        $this->em->merge($user);
    }
} 