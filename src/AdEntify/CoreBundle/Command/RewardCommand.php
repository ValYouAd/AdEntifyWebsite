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

    #endregion

    protected function configure ()
    {
        $this->setName('adentify:reward')
            ->setDescription('Check if a task waiting to be done');
    }

    protected function execute (InputInterface $input, OutputInterface $output)
    {
        $this->setup();

        $sql = 'SELECT u.id as userId, b.id as brandId, t.id as tagId, b.tag_required_addict_reward FROM users as u
            INNER JOIN tags as t ON (t.owner_id = u.id) INNER JOIN tag_points as tp ON (tp.tag_id = t.id)
            INNER JOIN brands as b ON (t.brand_id = b.id) WHERE NOT EXISTS(SELECT r.id FROM rewards as r
            WHERE r.owner_id = u.id AND r.brand_id = b.id) GROUP BY u.id HAVING COUNT(tp.id) >= b.tag_required_addict_reward';

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
                $tag = $this->em->getRepository('AdEntifyCoreBundle:Tag')->find($newAddictedUser['tagId']);
                $reward = new Reward();
                $reward->setBrand($brand)->setOwner($user)->setCanLoose(false)->setType(Reward::TYPE_ADDICT);

                $this->em->persist($reward);
                $this->em->flush();

                /*$sql = "INSERT INTO rewards (id, venue_id, product_id, person_id, brand_id, owner_id, type, can_loose, productType_id, win_at)
                    VALUES (NULL, NULL, NULL, NULL, :brandId, :userId, :type, :canLoose, NULL, :currentDateTime)";
                $this->em->getConnection()->executeUpdate($sql, array(
                    'type' => Reward::TYPE_ADDICT,
                    'canLoose' => false,
                    'currentDateTime' => date("Y-m-d H:i:s"),
                    'userId' => $newAddictedUser['userId'],
                    'brandId' => $newAddictedUser['brandId']
                ));*/

                $this->em->getRepository('AdEntifyCoreBundle:Action')->createAction(Action::TYPE_REWARD_NEW,
                    $user, $user, array($tag->getPhoto()), Action::getVisibilityWithPhotoVisibility($tag->getPhoto()->getVisibilityScope()), $tag->getPhoto()->getId(),
                    get_class($tag->getPhoto()), true, 'newReward', array('type' => $reward->getType()), null, $brand);
            }

            $output->writeln(sprintf('%s nouvelles récompenses', count($newAddictedUsers)));
        }
    }

    /**
     * Setup fields
     */
    private function setup()
    {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
    }
} 