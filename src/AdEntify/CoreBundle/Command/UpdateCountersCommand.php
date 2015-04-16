<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 12/12/2013
 * Time: 11:03
 */

namespace AdEntify\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCountersCommand extends ContainerAwareCommand
{
    protected $em;

    protected function configure () {
        $this->setName('adentify:update-counters')
            ->setDescription('Update counters');
    }

    protected function execute (InputInterface $input, OutputInterface $output) {
        $this->setup();

        $output->writeln('Update users');
        $sql = 'UPDATE users as u SET tags_count = (SELECT COUNT(t.id) FROM tags as t WHERE t.owner_id = u.id AND t.deleted_at IS NULL),
                    photos_count = (SELECT COUNT(p.id) FROM photos as p WHERE p.owner_id = u.id AND p.deleted_at IS NULL AND p.status = "ready"),
                    followers_count = (SELECT COUNT(f.following_id) FROM users_followings as f WHERE f.following_id = u.id),
                    followings_count = (SELECT COUNT(f.follower_id) FROM users_followings as f WHERE f.follower_id = u.id)';
        $this->em->getConnection()->executeUpdate($sql);

        $output->writeln('Update brands');
        $sql = 'UPDATE brands as b SET tags_count = (SELECT COUNT(t.id) FROM tags as t WHERE t.brand_id = b.id AND t.deleted_at IS NULL),
                    followers_count = (SELECT COUNT(bu.user_id) FROM brand_user as bu WHERE bu.brand_id = b.id)';
        $this->em->getConnection()->executeUpdate($sql);

        $output->writeln('Update tags count');
        $sql = 'UPDATE photos as p SET tags_count = (SELECT count(t.id) FROM tags as t WHERE t.photo_id = p.id AND t.deleted_at IS NULL 
                    AND t.waiting_validation = TRUE AND t.validation_status = \'granted\')';
        $this->em->getConnection()->executeUpdate($sql);

        $output->writeln('Flush modifications');
        $this->em->flush();
        $output->writeln('Done!');
    }

    /**
     * Setup fields
     */
    private function setup() {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
    }
} 