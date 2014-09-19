<?php

namespace AdEntify\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\Query\ResultSetMapping;

class BrandsSynchroCommand extends ContainerAwareCommand
{
    protected $em_adEntify;
    protected $conn;

    protected function configure ()
    {
        $this->setName('adentify:brands-synchro')
             ->setDescription('Synchronize brands from Adentify\'s database to LBJ\'s database');
    }

    protected function execute (InputInterface $input, OutputInterface $output)
    {
        $this->em_adEntify = $this->getContainer()->get('doctrine')->getEntityManager('default');
        $this->conn = $this->getContainer()->get('doctrine')->getConnection('LBJ');

        $rsm = new ResultSetMapping;
        $rsm->addScalarResult('id', 'id');
        $rsm->addScalarResult('name', 'name');
        $rsm->addScalarResult('slug', 'slug');
        $rsm->addScalarResult('original_logo_url', 'logo');
        $rsm->addScalarResult('description', 'description');
        $rsm->addScalarResult('facebook_url', 'facebook_url');
        $rsm->addScalarResult('twitter_url', 'twitter_url');
        $rsm->addScalarResult('website_url', 'website_url');

        //get all the brands name from LBJ's database and join them into one string
        $brands_lbj = $this->conn->executeQuery('SELECT * FROM brand')->fetchAll();
        $i = 0;
        $brands_name_lbj = array();
        foreach($brands_lbj as $brand_lbj)
            $brands_name_lbj[$i++] = $brand_lbj['name'];
        $brands_name_lbj = join('", "', $brands_name_lbj);

        //get all the brands, from AdEntify, which are not in the LBJ's database yet
        $brands = $this->em_adEntify->createNativeQuery('SELECT * FROM brands WHERE original_logo_url IS NOT NULL AND name NOT IN ("'.$brands_name_lbj.'")', $rsm)->getResult();

        //insert the brands from AdEntify into LBJ's database
        $count = 0;
        foreach($brands as $brand) {
            $this->conn->insert('brand', array(
                'name' => $brand['name'],
                'slug' => $brand['slug'],
                'logo' => $brand['logo'],
                'description' => $brand['description'],
                'facebook_url' => $brand['facebook_url'],
                'twitter_url' => $brand['twitter_url'],
                'website_url' => $brand['website_url']
            ));
            $count++;
        }

        $output->writeln(date('Y-m-d').': '.$count.' rows added!');
    }
}