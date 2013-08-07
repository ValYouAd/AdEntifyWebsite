<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 02/05/2013
 * Time: 12:08
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class OAuthClientCreateCommand extends ContainerAwareCommand
{
    protected function configure ()
    {
        $this
            ->setName('adentify:oauth:client:create')
            ->setDescription('Creates a new client')
            ->addOption('redirect-uri', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Sets the redirect uri. Use multiple times to set multiple uris.', null)
            ->addOption('grant-type', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Set allowed grant type. Use multiple times to set multiple grant types', null)
        ;
    }

    protected function execute (InputInterface $input, OutputInterface $output)
    {
        $clientManager = $this->getContainer()->get('fos_oauth_server.client_manager.default');
        $client = $clientManager->createClient();
        $client->setRedirectUris($input->getOption('redirect-uri'));
        $client->setAllowedGrantTypes($input->getOption('grant-type'));
        $clientManager->updateClient($client);

        $output->writeln(sprintf('Added a new client with  public id <info>%s</info>.', $client->getPublicId()));
    }
}