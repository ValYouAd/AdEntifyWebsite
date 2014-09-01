<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 01/09/2014
 * Time: 17:26
 */

namespace AdEntify\CoreBundle\Util;


use AdEntify\CoreBundle\Entity\Tag;

class TagValidator
{
    /**
     * Check if tag data is valid for the current tag type (place, person, product)
     *
     * @param Tag $tag
     * @return bool
     */
    public static function isValidTag(Tag $tag) {
        if (!$tag->getType())
            return false;

        switch($tag->getType()) {
            case Tag::TYPE_BRAND:
            case Tag::TYPE_PRODUCT:
                return true;
            case Tag::TYPE_PERSON:
                return $tag->getPerson() ? true : 'error.person.invalidTag';
            case Tag::TYPE_PLACE:
                return $tag->getVenue() ? true : 'error.venue.invalidTag';
            default:
                return true;
        }
    }
} 