<?php

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ActionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ActionRepository extends EntityRepository
{
    /**
     * Create action and notification if necessary
     *
     * @param $actionType
     * @param User $owner
     * @param User $author
     * @param null $photos
     * @param string $visibility
     * @param null $linkedObjectId
     * @param null $linkedObjectType
     * @param bool $createNotification
     * @param null $message
     * @param null $messageOptions
     * @return array
     */
    public function createAction($actionType, User $author = null, User $target = null, $photos = null, $visibility = Action::VISIBILITY_PUBLIC,
                                 $linkedObjectId = null, $linkedObjectType = null, $createNotification = true,
                                 $message = null, $messageOptions = null)
    {
        $action = null;

        // Notification
        $notification = null;
        if ($createNotification) {
            $notification = new Notification();
            $notification->setType($actionType);
            $this->getEntityManager()->persist($notification);
        }

        if ($author) {
            $action = new Action();
            $action->setType($actionType)->setAuthor($author)->setVisibility($visibility);
            if ($notification)
                $notification->setAuthor($author);
        }
        if ($photos) {
            foreach($photos as $photo) {
                if ($action)
                    $action->addPhoto($photo);
                if ($notification)
                    $notification->addPhoto($photo);
            }
        }
        if ($target) {
            if ($action)
                $action->setTarget($target);
            if ($notification)
                $notification->setOwner($target);
        }
        if ($linkedObjectId) {
            if ($action)
                $action->setLinkedObjectId($linkedObjectId);
            if ($notification)
                $notification->setObjectId($linkedObjectId);
        }
        if ($linkedObjectType) {
            if ($action)
                $action->setLinkedObjectType($linkedObjectType);
            if ($notification)
                $notification->setObjectType($linkedObjectType);
        }
        if ($message) {
            if ($action)
                $action->setMessage('action.'.$message);
            if ($notification)
                $notification->setMessage('notification.'.$message);
        }
        if ($messageOptions) {
            if ($action)
                $action->setMessageOptions($messageOptions);
            if ($notification)
                $notification->setMessageOptions($messageOptions);
        }

        if ($action) {
            $this->getEntityManager()->persist($action);
        }

        // Check if notification has owner
        if (!$notification->getOwner())
            $notification = null;

        $this->getEntityManager()->flush();

        return array(
            'action' => $action,
            'notification' => $notification
        );
    }
}
