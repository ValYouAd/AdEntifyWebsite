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
    public function deleteLinkedData($photo)
    {
        $em = $this->getEntityManager();

        // Delete actions related to this photo
        $actions = $em->createQuery('SELECT action FROM AdEntify\CoreBundle\Entity\Action action
                    LEFT JOIN action.photos photo WHERE photo.id = :photoId OR (action.linkedObjectId = :photoId AND action.linkedObjectType = :linkedObjectType)')
            ->setParameters(array(
                ':photoId' => $photo->getId(),
                'linkedObjectType' => 'AdEntify\CoreBundle\Entity\Photo'
            ))->getResult();
        if (count($actions) > 0) {
            foreach($actions as $action) {
                $em->remove($action);
            }
        }

        // Delete notifications
        $notifications = $em->createQuery('SELECT notif FROM AdEntify\CoreBundle\Entity\Notification notif
                    LEFT JOIN notif.photos photo WHERE photo.id = :photoId OR (notif.objectId = :photoId AND notif.objectType = :linkedObjectType)')
            ->setParameters(array(
                ':photoId' => $photo->getId(),
                'linkedObjectType' => 'AdEntify\CoreBundle\Entity\Photo'
            ))->getResult();
        if (count($notifications) > 0) {
            foreach($notifications as $notification) {
                $em->remove($notification);
            }
        }
    }

    public function getPhotos(User $user, $page, $limit = 10)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('p')
            ->leftJoin('p.tags', 't')
            ->orderBy('p.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit);
        if ($user->getBrand())
        {
            $qb->where('t.brand = :brand')
                ->setParameters(array(
                    ':brand' => $user->getBrand()
                ));
        }
        else
        {
            $qb->where('p.owner = :user')
                ->setParameters(array(
                    ':user' => $user
                ));
        }
        $photos = new Paginator($qb);
        $c = count($photos);
        return array(
            'photos' => $photos,
            'count' => $c,
            'pageLimit' => $limit
        );
    }
}