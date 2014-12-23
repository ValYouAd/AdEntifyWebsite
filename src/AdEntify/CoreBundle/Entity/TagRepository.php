<?php
/**
 * Created by PhpStorm.
 * User: huas
 * Date: 02/12/2014
 * Time: 15:40
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;
use AdEntify\CoreBundle\Entity\User;
use Doctrine\ORM\Tools\Pagination\Paginator;

class TagRepository extends EntityRepository{

    public function countBySelector($profile, $selector, $distinct = '')
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('COUNT('.$distinct.' t.'.$selector.')');

        if (is_a($profile, 'AdEntify\CoreBundle\Entity\Brand'))
        {
            $qb->where('t.brand = :brand')
                ->setParameters(array(
                    'brand' => $profile
            ));
        }
        else
        {
            $qb->leftJoin('t.photo', 'p')
                ->where('p.owner = :user')
                ->setParameters(array(
                    'user' => $profile
                ));
        }
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findTagsByPhoto($photo)
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('t')
            ->orderBy('t.createdAt', 'DESC')
            ->where('t.photo = :photo')
            ->setParameters(array(
                    ':photo' => $photo
            ));
        return $qb->getQuery();
    }

    public function getTaggersCountByPhoto($photo)
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('COUNT(DISTINCT t.owner)')
            ->where('t.photo = :photo')
            ->setParameters(array(
                'photo' => $photo
            ));
        return $qb->getQuery()->getSingleScalarResult();
    }
}