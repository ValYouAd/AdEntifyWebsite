<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 17/04/2014
 * Time: 12:05
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PhotoRepository extends EntityRepository
{
    public function getPhotos($profile, $options = array())
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('p')
            ->leftJoin('p.tags', 't')
            ->orderBy('p.createdAt', 'DESC');

        if (is_a($profile, 'AdEntify\CoreBundle\Entity\Brand')) {
            $qb->where('t.brand = :brand')
                ->setParameter(':brand', $profile);
        } else {
            $qb->where('p.owner = :user')
                ->setParameter(':user', $profile);
        }

        if (array_key_exists('daterange', $options)) {
            $dates = explode(' - ', $options['daterange']);
            $from = new \DateTime($dates[0]);
            $to = new \DateTime($dates[1]);

            $qb->andwhere('p.createdAt >= :from')
                ->andWhere('p.createdAt <= :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to);
        }

        if (array_key_exists('source', $options)) {
            $qb2 = $this->getEntityManager()->getRepository('AdEntifyCoreBundle:Analytic')->createQueryBuilder('a')
                ->select('a.id')
                ->where('a.photo = p.id')
                ->andWhere($qb->expr()->like('a.sourceUrl', ':source'))
                ->setMaxResults(1);

            $qb->andWhere($qb->expr()->exists($qb2->getDQL()))
                ->setParameter('source', '%'.$options['source'].'%');
        }

        return $qb->getQuery();
    }
}