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
                            WHERE person.facebookId IN (:facebookIds) AND person.facebookId NOT IN (:actualFacebookFriendsIds)')
                            ->setParameters(array(
                                ':facebookIds' => $facebookIds,
                                ':actualFacebookFriendsIds' => $actualFacebookFriendsIds
                            ))
                            ->getResult();
                        // Get existing users
                        $users = $this->_em->createQuery('SELECT u FROM AdEntify\CoreBundle\Entity\User u
                            WHERE u.facebookId IN (:facebookIds) AND u.facebookId NOT IN (:actualFacebookFriendsIds)')
                            ->setParameters(array(
                                ':facebookIds' => $facebookIds,
                                ':actualFacebookFriendsIds' => $actualFacebookFriendsIds
                            ))
                            ->getResult();

                        foreach($friends['data'] as $friend) {
                            // Check if isnt already a friend
                            if (in_array($friend['id'], $actualFacebookFriendsIds))
                                continue;

                            $foundPerson = null;
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
                                foreach ($users as $user) {
                                    if ($user->getFacebookId() == $friend['id']) {
                                        $person->setUser($user);
                                        break;
                                    }
                                }

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
}