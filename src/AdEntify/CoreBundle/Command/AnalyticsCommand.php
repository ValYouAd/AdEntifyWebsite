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

    protected function configure()
    {
        $this->setName('adentify:analytics')
            ->setDescription('Consolidate analytics');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->setup();

        $output->writeln('Update photos analytics');
        $sql = 'UPDATE photos as p SET
            views_count = (SELECT COUNT(a.id) FROM analytics as a WHERE a.photo_id = p.id AND a.action = :view AND a.element = :photoElement),
            hovers_count = (SELECT COUNT(a.id) FROM analytics as a WHERE a.photo_id = p.id AND a.action = :hover AND a.element = :photoElement),
            tags_hovers_count = (SELECT COUNT(a.id) FROM analytics as a LEFT JOIN tags as t ON a.tag_id = t.id WHERE t.photo_id = p.id AND a.action = :hover AND a.element = :tagElement),
            tags_clicks_count = (SELECT COUNT(a.id) FROM analytics as a LEFT JOIN tags as t ON a.tag_id = t.id WHERE t.photo_id = p.id AND a.action = :click AND a.element = :tagElement),
            hovers_percentage = ((p.hovers_count / NULLIF(p.views_count, 0)) * 100),
            tags_hovers_percentage = (((p.tags_hovers_count / (SELECT COUNT(t.id) FROM tags as t WHERE t.photo_id = p.id)) / NULLIF(p.hovers_count, 0)) * 100),
            interaction_time = (SELECT AVG(a.action_value) FROM analytics as a WHERE a.photo_id = p.id AND a.action = :interaction AND a.element = :photoElement AND a.action_value IS NOT NULL)';
        $this->em->getConnection()->executeUpdate($sql, array(
            'photoElement' => Analytic::ELEMENT_PHOTO,
            'tagElement' => Analytic::ELEMENT_TAG,
            'hover' => Analytic::ACTION_HOVER,
            'click' => Analytic::ACTION_CLICK,
            'view' => Analytic::ACTION_VIEW,
            'interaction' => Analytic::ACTION_INTERACTION
        ));

        $this->em->flush();

        $output->writeln('Update tags analytics');
        $sql = 'UPDATE tags as t SET
            hovers_count = (SELECT COUNT(a.id) FROM analytics as a WHERE t.id = a.tag_id AND a.action = :hover AND a.element = :tagElement),
            clicks_count = (SELECT COUNT(a.id) FROM analytics as a WHERE t.id = a.tag_id AND a.action = :click AND a.element = :tagElement),
            hovers_percentage = ((t.hovers_count / NULLIF((SELECT COUNT(a.id) FROM analytics as a WHERE t.photo_id = a.photo_id AND a.action = :photoView AND a.element = :photoElement), 0)) * 100),
            clicks_percentage = ((t.clicks_count / NULLIF(t.hovers_count, 0)) * 100),
            interaction_time = (SELECT AVG(a.action_value) FROM analytics as a WHERE t.id = a.tag_id AND a.action = :interaction AND a.element = :tagElement AND a.action_value IS NOT NULL)';
        $this->em->getConnection()->executeUpdate($sql, array(
            'photoElement' => Analytic::ELEMENT_PHOTO,
            'tagElement' => Analytic::ELEMENT_TAG,
            'hover' => Analytic::ACTION_HOVER,
            'click' => Analytic::ACTION_CLICK,
            'interaction' => Analytic::ACTION_INTERACTION,
            'photoView' => Analytic::ACTION_VIEW
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