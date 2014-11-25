<?php

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * AnalyticRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AnalyticRepository extends EntityRepository
{
    public function isAlreadyTracked(Analytic $analytic)
    {
        $yesterday = new \DateTime();
        $yesterday = $yesterday->sub(new \DateInterval('P1D'));

        $qb = $this->createQueryBuilder('analytic');
        $qb->where('analytic.ipAddress = :ipAddress')
            ->andWhere('analytic.createdAt > :yesterday')
            ->andWhere('analytic.element = :element')
            ->andWhere('analytic.action = :action');

        $parameters = array(
            ':ipAddress' => $analytic->getIpAddress(),
            ':action' => $analytic->getAction(),
            ':element' => $analytic->getElement(),
            ':yesterday' => $yesterday
        );

        if ($analytic->getTag()) {
            $qb->leftJoin('analytic.tag', 'tag');
            $qb->andWhere('tag.id = :tagId');
            $parameters['tagId'] = $analytic->getTag()->getId();
        }
        if ($analytic->getUser()) {
            $qb->leftJoin('analytic.user', 'user');
            $qb->andWhere('user.id = :userId');
            $parameters['userId'] = $analytic->getUser()->getId();
        }
        if ($analytic->getPhoto()) {
            $qb->leftJoin('analytic.photo', 'photo');
            $qb->andWhere('photo.id = :photoId');
            $parameters['photoId'] = $analytic->getPhoto()->getId();
        }
        if ($analytic->getLink()) {
            $qb->andWhere('analytic.link = :link');
            $parameters['link'] = $analytic->getLink();
        }
        if ($analytic->getPlatform()) {
            $qb->andWhere('analytic.platform = :platform');
            $parameters['platform'] = $analytic->getPlatform();
        }

        $analytic = $qb->setMaxResults(1)->setParameters($parameters)->getQuery()->getOneOrNullResult();

        return $analytic ? true : false;
    }
}
