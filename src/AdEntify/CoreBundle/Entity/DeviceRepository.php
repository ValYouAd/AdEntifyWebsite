<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 16/09/2014
 * Time: 11:22
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class DeviceRepository extends EntityRepository
{
    const OS_APPLE = 'Apple';

    /**
     * Get devices for user by OS
     *
     * @param $operatingSystem
     * @param User $user
     * @return array
     */
    public function getDevicesByOS($operatingSystem, User $user) {
        return $this->createQueryBuilder('d')->where('d.operatingSystem like :operatingSystem')
            ->andWhere('d.owner = :user')
            ->setParameters(array(
                'user' => $user,
                'operatingSystem' => '%'.$operatingSystem.'%'
            ))->getQuery()->getResult();
    }
} 