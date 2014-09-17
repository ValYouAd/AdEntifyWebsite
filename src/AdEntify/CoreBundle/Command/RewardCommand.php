<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 21/01/2014
 * Time: 13:33
 */

namespace AdEntify\CoreBundle\Command;

use AdEntify\CoreBundle\Entity\Action;
use AdEntify\CoreBundle\Entity\Reward;
use AdEntify\CoreBundle\Entity\TagPoint;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RewardCommand extends ContainerAwareCommand
{
    #region fields

    protected $em;
    protected $pushNotification;
    protected $translator;

    #endregion

    protected function configure ()
    {
        $this->setName('adentify:reward')
            ->setDescription('Check if a task waiting to be done');
    }

    protected function execute (InputInterface $input, OutputInterface $output)
    {
        $this->setup();

        // Check new addict fans
        $sql = 'SELECT u.id as userId, b.id as brandId, b.tag_required_addict_reward, COUNT(tp.id) FROM users as u
            INNER JOIN tags as t ON (t.owner_id = u.id) INNER JOIN tag_points as tp ON (tp.tag_id = t.id)
            INNER JOIN brands as b ON (t.brand_id = b.id) WHERE tp.status = :status AND NOT EXISTS(SELECT r.id FROM rewards as r
            WHERE r.owner_id = u.id AND r.brand_id = b.id)
            GROUP BY u.id, b.id HAVING COUNT(tp.id) >= b.tag_required_addict_reward';

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('userId', 'userId', 'integer');
        $rsm->addScalarResult('brandId', 'brandId', 'integer');
        $rsm->addScalarResult('tagId', 'tagId', 'integer');

        $newAddictedUsers = $this->em->createNativeQuery($sql, $rsm)->setParameters(
            array(
                'status' => TagPoint::STATUS_CREDITED
            )
        )->getResult();

        if (count($newAddictedUsers) > 0) {
            foreach ($newAddictedUsers as $newAddictedUser) {
                $user = $this->em->getRepository('AdEntifyCoreBundle:User')->find($newAddictedUser['userId']);
                $brand = $this->em->getRepository('AdEntifyCoreBundle:Brand')->find($newAddictedUser['brandId']);
                $reward = new Reward();
                $reward->setBrand($brand)->setOwner($user)->setCanLoose(false)->setType(Reward::TYPE_ADDICT);

                $this->em->persist($reward);
                $this->em->flush();

                $this->em->getRepository('AdEntifyCoreBundle:Action')->createAction(Action::TYPE_REWARD_NEW,
                    $user, $user, null, Action::VISIBILITY_PUBLIC, null,
                    null, true, 'newReward', array('type' => $reward->getType()), null, $brand);

                $options = $this->pushNotification->getOptions('pushNotification.brandReward', array(
                    '%brand%' => $brand->getName(),
                    '%reward%' => $reward->getType()
                ), array(
                    'brandId' => $brand->getId()
                ));
                $this->pushNotification->sendToUser($user, $options);
            }

            $output->writeln(sprintf('%s nouvelles récompenses addict', count($newAddictedUsers)));
        }

        // Get brands with tags
        $brands = $this->em->createQuery('SELECT brand FROM AdEntifyCoreBundle:Brand brand WHERE brand.tagsCount > 0')->getResult();
        foreach($brands as $brand) {
            // Get users with points
            $sql = 'SELECT SUM(tp.points) as points, u.id as userId, b.id as brandId FROM users as u
            INNER JOIN tags as t ON (t.owner_id = u.id) INNER JOIN tag_points as tp ON (tp.tag_id = t.id)
            INNER JOIN brands as b ON (t.brand_id = b.id) WHERE tp.status = :status AND b.id = :brand AND EXISTS(SELECT r.id FROM rewards as r
            WHERE r.owner_id = u.id AND r.brand_id = b.id) GROUP BY u.id ORDER BY points DESC';

            $rsm = new ResultSetMapping();
            $rsm->addScalarResult('userId', 'userId', 'integer');
            $rsm->addScalarResult('brandId', 'brandId', 'integer');
            $rsm->addScalarResult('tagId', 'tagId', 'integer');

            $addictedUsers = $this->em->createNativeQuery($sql, $rsm)->setParameters(
                array(
                    'status' => TagPoint::STATUS_CREDITED,
                    'brand' => $brand->getId()
                )
            )->getResult();

            $totalUsersWithPoints = count($addictedUsers);
            if ($totalUsersWithPoints > 0) {
                $maxGoldMembers = $this->getMaxFans($totalUsersWithPoints, $brand->getGoldFansPercentage(), Reward::MAX_GOLD_FANS);
                $maxSilverMembers = $this->getMaxFans($totalUsersWithPoints, $brand->getGoldFansPercentage(), Reward::MAX_SILVER_FANS);
                $maxBronzeMembers = $this->getMaxFans($totalUsersWithPoints, $brand->getGoldFansPercentage(), Reward::MAX_BRONZE_FANS);

                $goldFans = array();
                $silverFans = array();
                $bronzeFans = array();

                for ($i=0; $i<count($addictedUsers); $i++) {
                    if ($maxGoldMembers > 0) {
                        $goldFans[] = $addictedUsers[$i]['userId'];
                        $maxGoldMembers--;
                    } else if ($maxSilverMembers > 0) {
                        $silverFans[] = $addictedUsers[$i]['userId'];
                        $maxSilverMembers--;
                    } else if ($maxBronzeMembers > 0) {
                        $bronzeFans[] = $addictedUsers[$i]['userId'];
                        $maxBronzeMembers--;
                    }
                }

                $this->deleteDuplicateFans($brand, $goldFans, Reward::TYPE_GOLD);
                $this->deleteDuplicateFans($brand, $silverFans, Reward::TYPE_SILVER);
                $this->deleteDuplicateFans($brand, $bronzeFans, Reward::TYPE_BRONZE);

                $this->newRewards(array(
                    array(
                        'fans' => $goldFans,
                        'rewardType' => Reward::TYPE_GOLD
                    ),
                    array(
                        'fans' => $silverFans,
                        'rewardType' => Reward::TYPE_SILVER
                    ),
                    array(
                        'fans' => $bronzeFans,
                        'rewardType' => Reward::TYPE_BRONZE
                    ),
                ), $brand);
            }

            $output->writeln($brand->getName());
            $output->writeln($totalUsersWithPoints);
        }

        $this->em->flush();
    }

    /**
     * Delete old fans for a reward type and a brand
     *
     * @param $brand
     * @param $fans
     * @param $rewardType
     */
    private function deleteDuplicateFans($brand, &$fans, $rewardType)
    {
        if (count($fans) > 0) {
            $oldFans = $this->em->createQuery('SELECT user.id FROM AdEntifyCoreBundle:User user LEFT JOIN user.rewards reward
            WHERE reward.brand = :brandId AND reward.type = :rewardType AND user.id NOT IN (:fans)')
                ->setParameters(array(
                    'brandId' => $brand->getId(),
                    'rewardType' => $rewardType,
                    'fans' => $fans
                ))
                ->getArrayResult();

            $currentFans = $this->em->createQuery('SELECT user.id FROM AdEntifyCoreBundle:User user LEFT JOIN user.rewards reward
            WHERE reward.brand = :brandId AND reward.type = :rewardType AND user.id IN (:fans)')
                ->setParameters(array(
                    'brandId' => $brand->getId(),
                    'rewardType' => $rewardType,
                    'fans' => $fans
                ))
                ->getArrayResult();

            $fans = count($currentFans) > 0 ? array_diff(array_values($currentFans[0]), $fans) : $fans;

            if (count($oldFans) > 0 && count($oldFans[0]) > 0) {

                $connection = $this->em->getConnection();
                $connection->executeUpdate('DELETE FROM rewards WHERE owner_id IN (:oldFans) AND brand_id = :brandId', array(
                    'oldFans' => implode(',', array_values($oldFans[0])),
                    'brandId' => $brand->getId()
                ));
            }
        }
    }

    /**
     * Get max fans
     *
     * @param $percentage
     * @param $totalUsers
     * @param $maxUsers
     * @return float
     */
    private function getMaxFans($percentage, $totalUsers, $maxUsers)
    {
        $maxFans = ceil(($totalUsers * $percentage) / 100);
        return $maxFans > $maxUsers ?  $maxUsers : $maxFans;
    }

    /**
     * @param $fansByRewardType
     * @param $brand
     */
    private function newRewards($fansByRewardType, $brand)
    {
        foreach($fansByRewardType as $fans) {
            foreach($fans['fans'] as $userId) {
                $this->newReward($userId, $brand, $fans['rewardType']);
            }
        }
    }

    /**
     * @param $userId
     * @param $brand
     * @param $rewardType
     * @param bool $canLoose
     */
    private function newReward($userId, $brand, $rewardType, $canLoose = true)
    {
        $user = $this->em->getRepository('AdEntifyCoreBundle:User')->find($userId);
        $reward = new Reward();
        $reward->setBrand($brand)->setOwner($user)->setCanLoose($canLoose)->setType($rewardType);
        $this->em->persist($reward);

        $this->em->getRepository('AdEntifyCoreBundle:Action')->createAction(Action::TYPE_REWARD_NEW,
            $user, $user, null, Action::VISIBILITY_PUBLIC, null,
            null, true, 'newReward', array('type' => $reward->getType()), null, $brand);
    }

    /**
     * Setup fields
     */
    private function setup()
    {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->pushNotification = $this->getContainer()->get('ad_entify_core.pushNotifications');
        $this->translator = $this->getContainer()->get('translator');
    }
} 