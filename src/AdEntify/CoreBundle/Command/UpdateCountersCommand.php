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

    protected function configure ()
    {
        $this->setName('adentify:update-counters')
            ->setDescription('Update counters');
    }

    protected function execute (InputInterface $input, OutputInterface $output)
    {
        $this->setup();

        $output->writeln('Load users');
        $users = $this->em->getRepository('AdEntifyCoreBundle:User')->findAll();
        foreach($users as $user) {
            $user->setFollowersCount(count($user->getFollowers()));
            $user->setFollowingsCount(count($user->getFollowings()));
            $user->setTagsCount(count($user->getTags()));
            $user->setPhotosCount(count($user->getPhotos()));
            $this->em->merge($user);
        }

        $output->writeln('Flush modifications');
        $this->em->flush();
        $output->writeln('Done!');
    }

    /**
     * Setup fields
     */
    private function setup()
    {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
    }
} 