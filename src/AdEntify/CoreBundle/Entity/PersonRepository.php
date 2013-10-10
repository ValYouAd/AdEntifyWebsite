<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 24/06/2013
 * Time: 14:33
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class PersonRepository extends EntityRepository
{
    public function createAndLinkToExistingUser($firstname, $lastname, $name, $profilePictureUrl, $serviceId, $source)
    {
        $person = new Person();
        if ($firstname && !empty($firstname))
            $person->setFirstname($firstname);
        if ($lastname && !empty($lastname))
            $person->setLastname($lastname);
        if ($name && !empty($name))
            $person->setName($name);
        if ($profilePictureUrl && !empty($profilePictureUrl))
            $person->setProfilePictureUrl($profilePictureUrl);

        // Link to existing user
        $user = null;
        if ($source == 'facebook' && !empty($serviceId)) {
            $person->setFacebookId($serviceId);
            $user = $this->getEntityManager()->getRepository('AdEntifyCoreBundle:User')->findOneBy(array(
                'facebookId' => $person->getFacebookId()
            ));
        }
        else if ($source == 'instagram' && !empty($serviceId)) {
            $person->setInstagramId($serviceId);
            $user = $this->getEntityManager()->createQuery('SELECT user FROM AdEntify\CoreBundle\Entity\User user
                LEFT JOIN user.oAuthUserInfos oauthUserInfo
                WHERE oauthUserInfo.serviceName = "instagram" AND oauthUserInfo.serviceUserId = :instagramUserId')
                ->setMaxResults(1)
                ->setParameters(array(
                    ':instagramUserId' => $person->getInstagramId()
                ))
                ->getOneOrNullResult();
        }

        if ($user) {
            $person->setUser($user);
            if (!$person->getFirstname() && $user->getFirstname())
                $person->setFirstname($user->getFirstname());
            if (!$person->getLastname() && $user->getLastname())
                $person->setLastname($user->getLastname());
            if (!$person->getGender() && $user->getGender())
                $person->setGender($user->getGender());
        }

        $this->getEntityManager()->persist($person);
        $this->getEntityManager()->flush();
        return $person;
    }
}