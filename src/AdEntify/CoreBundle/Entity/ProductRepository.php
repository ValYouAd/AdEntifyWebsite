<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 17/04/2014
 * Time: 12:05
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ProductRepository extends EntityRepository
{
    public function searchProducts($query, $page, $limit, $brandId = 0)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where('p.name LIKE :query');
        $parameters = array(
            ':query' => '%'.$query.'%'
        );

        if ($brandId > 0) {
            $qb->andWhere('p.brand = :brandId');
            $parameters['brandId'] = $brandId;
        }

        return $qb->setParameters($parameters)->setMaxResults($limit)->setFirstResult(($page - 1) * $limit)->getQuery()->getResult();
    }
} 