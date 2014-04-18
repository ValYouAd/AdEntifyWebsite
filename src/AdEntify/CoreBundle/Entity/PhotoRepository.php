<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 17/04/2014
 * Time: 12:05
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

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
} 