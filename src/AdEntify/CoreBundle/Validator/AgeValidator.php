<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 09/01/2014
 * Time: 18:00
 */

namespace AdEntify\CoreBundle\Validator;

class AgeValidator
{
    /**
     * @param $object
     * @param $user
     * @return bool|true if older enough, false if too young
     */
    public static function validateAge($object, $user)
    {
        return AgeValidator::getAge($user) < $object->getMinAge() ? false : true;
    }

    private static function getAge($user)
    {
        return !$user->getBirthday() ? 0 : $user->getBirthday()->diff(new \DateTime())->y;
    }
} 