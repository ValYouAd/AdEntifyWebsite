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

    public function countBySelector(User $user, $selector, $distinct = '')
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('COUNT('.$distinct.' t.'.$selector.')');
        if ($user->getBrand())
        {
            $qb->where('t.brand = :brand')
                ->setParameters(array(
                    'brand' => $user->getBrand()
            ));
        }
        else
        {
            $qb->leftJoin('t.photo', 'p')
                ->where('p.owner = :user')
                ->setParameters(array(
                    'user' => $user
                ));
        }
        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findTagsByPhoto($photo, $page, $limit = 10)
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('t')
            ->orderBy('t.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->where('t.photo = :photo')
            ->setParameters(array(
                    ':photo' => $photo
            ));
        $tags = new Paginator($qb);
        $c = count($tags);
        return array(
            'tags' => $tags,
            'count' => $c,
            'pageLimit' => $limit
        );
    }
}