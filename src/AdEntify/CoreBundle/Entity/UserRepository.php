<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 24/06/2013
 * Time: 14:57
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

class UserRepository  extends EntityRepository
{
    /**
     * Update friends list if last update is older than 48 hours
     *
     * @param User $user
     * @param $fbApi
     */
    public function refreshFriends(User $user, $fbApi)
    {
        if ($user->getFacebookId()) {
            $hours = 0;
            if ($user->getLastFriendsListUpdate()) {
                $interval = $user->getLastFriendsListUpdate()->diff(new \DateTime());
                $hours = $interval->h + ($interval->d*24);
            }

            if (!$user->getLastFriendsListUpdate() || $hours >= 40) {
                try {
                    // Proceed knowing you have a logged in user who's authenticated.
                    $friends = $fbApi->api('/me/friends?fields=name,first_name,last_name,gender');
                    if (array_key_exists('data', $friends)) {
                        // Get all facebook friends id
                        $facebookIds = array();
                        foreach($friends['data'] as $friend) {
                            $facebookIds[] = $friend['id'];
                        }
                        // Get actual facebook friends id
                        $actualFacebookFriendsIds = $this->getFacebookFriendsIds($user->getFriends());
                        // Get existing persons
                        $persons = $this->_em->createQuery('SELECT person FROM AdEntify\CoreBundle\Entity\Person person
                            LEFT JOIN person.user user
                            WHERE person.facebookId IN (:facebookIds) AND (person.facebookId NOT IN (:actualFacebookFriendsIds) OR user IS NULL)')
                            ->setParameters(array(
                                ':facebookIds' => $facebookIds,
                                ':actualFacebookFriendsIds' => $actualFacebookFriendsIds
                            ))
                            ->getResult();
                        // Get existing users
                        $users = $this->_em->createQuery('SELECT u FROM AdEntify\CoreBundle\Entity\User u
                            WHERE u.facebookId IN (:facebookIds)')
                            ->setParameters(array(
                                ':facebookIds' => $facebookIds
                            ))
                            ->getResult();

                        foreach($friends['data'] as $friend) {
                            // Check if fb id isnt current user fb id
                            if ($friend['id'] == $user->getFacebookId())
                                continue;
                            $foundPerson = null;
                            // Check if isnt already a friend
                            if (in_array($friend['id'], $actualFacebookFriendsIds)) {
                                foreach ($persons as $person) {
                                    if ($person->getFacebookId() == $friend['id']) {
                                        $foundPerson = $person;
                                        break;
                                    }
                                }
                                if ($foundPerson && !$foundPerson->getUser())
                                    $this->linkPersonToUser($foundPerson, $users);
                                continue;
                            }

                            foreach ($persons as $person) {
                                if ($person->getFacebookId() == $friend['id']) {
                                    $foundPerson = $person;
                                    break;
                                }
                            }

                            // If person found, add to friend
                            if ($foundPerson) {
                                $user->addFriend($foundPerson);
                            // If not, create new person and add to friend
                            } else {
                                $person = new Person();
                                $person->setFacebookId($friend['id'])->setFirstname($friend['first_name'])
                                    ->setLastname($friend['last_name']);
                                if (array_key_exists('gender', $friend))
                                    $person->setGender($friend['gender']);

                                // Search if person isnt an AdEntify user
                                $this->linkPersonToUser($person, $users);

                                $user->addFriend($person);
                                $this->_em->persist($person);
                            }
                        }

                        $user->setLastFriendsListUpdate(new \DateTime());
                        $this->_em->merge($user);
                        $this->_em->flush();

                        return $facebookIds;
                    }
                } catch (FacebookApiException $e) {
                    return $this->getFacebookFriendsIds($user->getFriends());
                }
            }
            else {
                return $this->getFacebookFriendsIds($user->getFriends());
            }
        } else {
            return array(0);
        }
    }

    public function getFollowedBrandsIds($user)
    {
        return $this->getEntityManager()->createQuery('SELECT brand.id FROM AdEntifyCoreBundle:User u LEFT JOIN u.followedBrands brand
            WHERE u.id = :userId')->setParameters(array(
                'userId' => $user->getId(),
            ))->getArrayResult();
    }

    /**
     * Get array of facebook friends id
     *
     * @param $friends
     * @return array
     */
    private function getFacebookFriendsIds($friends)
    {
        $actualFacebookFriendsIds = array();
        if ($friends) {
            foreach ($friends as $friend) {
                if ($friend->getFacebookId())
                    $actualFacebookFriendsIds[] = $friend->getFacebookId();
            }
        }
        if (count($actualFacebookFriendsIds) == 0)
            $actualFacebookFriendsIds[] = 0;
        return $actualFacebookFriendsIds;
    }

    private function linkPersonToUser($person, $users) {

        foreach ($users as $user) {
            if ($user->getFacebookId() == $person->getFacebookId()) {
                $user->setPerson($person);
                $this->_em->merge($user);
            }
        }
    }
}