<?php
/**
 * Created by PhpStorm.
 * User: huas
 * Date: 02/12/2014
 * Time: 15:40
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;
use AdEntify\CoreBundle\Entity\Tag;


class TagRepository extends EntityRepository{

    public function countBrandTags(Brand $brand)
    {
        return $this->getEntityManager()->createQuery('SELECT COUNT(t.id)
                                                       FROM AdEntifyCoreBundle:Tag t
                                                       WHERE t.brand = :brand')
                ->setParameters(array(
                    ':brand' => $brand
                ))
                ->getSingleScalarResult();
    }

    public function countBrandTaggers(Brand $brand)
    {
        return $this->getEntityManager()->createQuery('SELECT COUNT(distinct t.owner)
                                                       FROM AdEntifyCoreBundle:Tag t
                                                       WHERE t.brand = :brand')
            ->setParameters(array(
                ':brand' => $brand
            ))
            ->getSingleScalarResult();
    }

    public function countBrandPhotos(Brand $brand)
    {
        return $this->getEntityManager()->createQuery('SELECT COUNT(distinct t.photo)
                                                       FROM AdEntifyCoreBundle:Tag t
                                                       WHERE t.brand = :brand')
            ->setParameters(array(
                ':brand' => $brand
            ))
            ->getSingleScalarResult();
    }
}