<?php
/**
 * Created by PhpStorm.
 * User: huas
 * Date: 26/11/2014
 * Time: 10:39
 */

namespace AdEntify\CoreBundle\Entity;


use Doctrine\ORM\EntityRepository;
use AdEntify\CoreBundle\Entity\User;


class UserProductProviderRepository extends EntityRepository{

    public function findByUser(User $user)
    {
        return $this->findBy(array(
            'users' => $user->getId()
        ));
    }
}