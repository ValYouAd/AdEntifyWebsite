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
        $qb->select('COUNT('.$distinct.' t.'.$selector.')')
            ->where('t.deletedAt IS NULL')
            ->andWhere('t.visible = true')
            ->andWhere('t.censored = false')
            ->andWhere('t.validationStatus != :denied');

        if (is_a($profile, 'AdEntify\CoreBundle\Entity\Brand')) {
            $qb->andWhere('t.brand = :brand')
                ->setParameters(array(
                    'brand' => $profile,
                    'denied' => Tag::VALIDATION_DENIED,
            ));
        } else {
            $qb->leftJoin('t.photo', 'p')
                ->andWhere('p.owner = :user')
                ->setParameters(array(
                    'user' => $profile,
                    'denied' => Tag::VALIDATION_DENIED,
                ));
        }

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findTagsByPhoto($photo)
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('t')
            ->orderBy('t.createdAt', 'DESC')
            ->where('t.deletedAt IS NULL')
            ->andWhere('t.visible = true')
            ->andWhere('t.censored = false')
            ->andWhere('t.validationStatus != :denied')
            ->andWhere('t.photo = :photo')
            ->setParameters(array(
                'photo' => $photo,
                'denied' => Tag::VALIDATION_DENIED,
            ));

        return $qb->getQuery();
    }

    public function getTaggersCountByPhoto($photo)
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('COUNT(DISTINCT t.owner)')
            ->where('t.deletedAt IS NULL')
            ->andWhere('t.visible = true')
            ->andWhere('t.censored = false')
            ->andWhere('t.validationStatus != :denied')
            ->andWhere('t.photo = :photo')
            ->setParameters(array(
                'photo' => $photo,
                'denied' => Tag::VALIDATION_DENIED,
            ));
        return $qb->getQuery()->getSingleScalarResult();
    }
}