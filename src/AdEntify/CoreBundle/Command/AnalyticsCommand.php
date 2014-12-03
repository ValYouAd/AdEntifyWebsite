<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 21/01/2014
 * Time: 13:33
 */

namespace AdEntify\CoreBundle\Command;

use AdEntify\CoreBundle\Entity\Action;
use AdEntify\CoreBundle\Entity\Analytic;
use AdEntify\CoreBundle\Entity\Reward;
use AdEntify\CoreBundle\Entity\TagPoint;
use Doctrine\ORM\Query\ResultSetMapping;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AnalyticsCommand extends ContainerAwareCommand
{
    #region fields

    protected $em;

    #endregion

    protected function configure ()
    {
	$this->setName('adentify:analytics')
	    ->setDescription('Consolidate analytics');
    }

    protected function execute (InputInterface $input, OutputInterface $output)
    {
	$this->setup();

	$output->writeln('Update photos analytics');
	$sql = 'UPDATE photos as p SET views_count = (SELECT COUNT(a.id) FROM analytics as a WHERE a.photo_id = p.id AND a.action = :view AND a.element = :photoElement),
		    hovers_count = (SELECT COUNT(a.id) FROM analytics as a WHERE a.photo_id = p.id AND a.action = :hover AND a.element = :photoElement),
		    tags_hovers_count = (SELECT COUNT(a.id) FROM analytics as a LEFT JOIN tags as t ON a.tag_id = t.id WHERE t.photo_id = p.id AND a.action = :hover AND a.element = :tagElement),
		    tags_clicks_count = (SELECT COUNT(a.id) FROM analytics as a LEFT JOIN tags as t ON a.tag_id = t.id WHERE t.photo_id = p.id AND a.action = :clic AND a.element = :tagElement)';
	$this->em->getConnection()->executeUpdate($sql, array(
	    'photoElement' => Analytic::ELEMENT_PHOTO,
	    'tagElement' => Analytic::ELEMENT_TAG,
	    'hover' => Analytic::ACTION_HOVER,
	    'clic' => Analytic::ACTION_CLICK,
	    'view' => Analytic::ACTION_VIEW
	));

	$this->em->flush();
    }

    /**
     * Setup fields
     */
    private function setup()
    {
	$this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
    }
}