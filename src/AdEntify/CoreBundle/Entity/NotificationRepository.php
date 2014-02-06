<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 06/02/2014
 * Time: 11:42
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class NotificationRepository extends EntityRepository
{
    /**
     * Remove existing notification
     *
     * @param User $author
     * @param User $target
     * @param null $linkedObjectId
     * @param null $linkedObjectType
     * @param Brand $brand
     */
    public function removeNotification(User $author = null, User $target = null, $linkedObjectId = null,
                                       $linkedObjectType = null, Brand $brand = null) {
        $notification = $this->getExistingNotification($author, $target, $linkedObjectId, $linkedObjectType, $brand);
        if ($notification) {
            $this->getEntityManager()->remove($notification);
        }
    }

    /**
     * Get existing notification
     *
     * @param $author
     * @param $target
     * @param null $linkedObjectId
     * @param null $linkedObjectType
     * @param null $brand
     * @return mixed
     */
    private function getExistingNotification($author, $target, $linkedObjectId = null, $linkedObjectType  = null, $brand = null)
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $parameters = array();

        $qb->select('Notification')
            ->from('AdEntifyCoreBundle:Notification', 'Notification')
            ->orderBy('Notification.id', 'DESC');

        if ($author) {
            $qb->andWhere($qb->expr()->eq('Notification.author', ':author'));
            $parameters['author'] = $author->getId();
        }
        if ($target) {
            $qb->andWhere($qb->expr()->eq('Notification.owner', ':owner'));
            $parameters['owner'] = $target->getId();
        }
        if ($linkedObjectId) {
            $qb->andWhere($qb->expr()->eq('Notification.objectId', ':objectId'));
            $parameters['objectId'] = $linkedObjectId;
        }
        if ($linkedObjectType) {
            $qb->andWhere($qb->expr()->eq('Notification.objectType', ':objectType'));
            $parameters['objectType'] = $linkedObjectType;
        }
        if ($brand) {
            $qb->andWhere($qb->expr()->eq('Notification.brand', ':brand'));
            $parameters['brand'] = $brand->getId();
        }

        $qb->setParameters($parameters)->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult();
    }
} 