<?php

namespace AdEntify\CoreBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * AnalyticRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AnalyticRepository extends EntityRepository
{
    public function isAlreadyTracked(Analytic $analytic)
    {
        $sinceDate = new \DateTime();
        switch ($analytic->getAction())
        {
            case Analytic::ACTION_INTERACTION:
            case Analytic::ACTION_HOVER:
                $sinceDate = $sinceDate->sub(new \DateInterval('PT5S'));
			    break;
            case Analytic::ACTION_VIEW:
            case Analytic::ACTION_CLICK:
            default:
                $sinceDate = $sinceDate->sub(new \DateInterval('PT1H'));
        }

        $qb = $this->createQueryBuilder('analytic');
        $qb->where('analytic.ipAddress = :ipAddress')
            ->andWhere('analytic.createdAt > :sinceDate')
            ->andWhere('analytic.element = :element')
            ->andWhere('analytic.action = :action');

        $parameters = array(
            ':ipAddress' => $analytic->getIpAddress(),
            ':action' => $analytic->getAction(),
            ':element' => $analytic->getElement(),
            ':sinceDate' => $sinceDate
        );

        if ($analytic->getTag()) {
            $qb->leftJoin('analytic.tag', 'tag');
            $qb->andWhere('tag.id = :tagId');
            $parameters['tagId'] = $analytic->getTag()->getId();
        }
        if ($analytic->getUser()) {
            $qb->leftJoin('analytic.user', 'user');
            $qb->andWhere('user.id = :userId');
            $parameters['userId'] = $analytic->getUser()->getId();
        }
        if ($analytic->getPhoto()) {
            $qb->leftJoin('analytic.photo', 'photo');
            $qb->andWhere('photo.id = :photoId');
            $parameters['photoId'] = $analytic->getPhoto()->getId();
        }
        if ($analytic->getLink()) {
            $qb->andWhere('analytic.link = :link');
            $parameters['link'] = $analytic->getLink();
        }
        if ($analytic->getPlatform()) {
            $qb->andWhere('analytic.platform = :platform');
            $parameters['platform'] = $analytic->getPlatform();
        }

        $analytic = $qb->setMaxResults(1)->setParameters($parameters)->getQuery()->getOneOrNullResult();

        return $analytic ? true : false;
    }

    public function getGraphLabels($photosViewsGraph, $options)
    {
        if ($options['dateInterval'] != 'P1D')
            return array_keys($photosViewsGraph);
        else
        {
            $i = 3;
            $result = array();
            foreach(array_keys($photosViewsGraph) as $key)
            {
                if ($i % 3 == 0)
                    $result[] = $key;
                else
                    $result[] = '';
                $i++;
            }
            return $result;
        }
    }

    public function getTotalAction($datas = array())
    {
        $result = 0;
        foreach($datas as $data)
            $result += $data;
        return $result;
    }

    public function findGlobalAnalyticsByUser($profile, &$options = array())
    {
        $this->getStatsPeriod($options);
        $photosViewsGraph = $this->parseDataForGraph($this->getElementCountByAction($profile, array_merge(array(
            'element' => Analytic::ELEMENT_PHOTO,
            'action' => Analytic::ACTION_VIEW,
            'graph' => true
        ), $options)), $options);

        $photosHoversGraph = $this->parseDataForGraph($this->getElementCountByAction($profile, array_merge(array(
            'element' => Analytic::ELEMENT_PHOTO,
            'action' => Analytic::ACTION_HOVER,
            'graph' => true
        ), $options)), $options);

        $photosClicksGraph = $this->parseDataForGraph($this->getElementCountByAction($profile, array_merge(array(
            'element' => Analytic::ELEMENT_TAG,
            'action' => Analytic::ACTION_CLICK,
            'graph' => true
        ), $options)), $options);

        $photosInteractionGraph = $this->parseDataForGraph($this->getAvgInteractionTime($profile, $options), $options);

        $analytics = array(
            'photosViews' => $this->getElementCountByAction($profile, array_merge(array(
                'element' => Analytic::ELEMENT_PHOTO,
                'action' => Analytic::ACTION_VIEW
            ), $options)),
            'photosHovers' => $this->getElementCountByAction($profile, array_merge(array(
                'element' => Analytic::ELEMENT_PHOTO,
                'action' => Analytic::ACTION_HOVER
            ), $options)),
            'tagsHovers' => $this->getElementCountByAction($profile, array_merge(array(
                'element' => Analytic::ELEMENT_TAG,
                'action' => Analytic::ACTION_HOVER
            ), $options)),
            'tagsClicks' => $this->getElementCountByAction($profile, array_merge(array(
                'element' => Analytic::ELEMENT_TAG,
                'action' => Analytic::ACTION_CLICK
            ), $options)),
            'photosViewsGraph' => array(
                'data' => $photosViewsGraph,
                'labels' => $this->getGraphLabels($photosViewsGraph, $options),
                'total' => $this->getTotalAction($photosViewsGraph)
            ),
            'photosHoversGraph' => array(
                'data' => $photosHoversGraph,
                'labels' => $this->getGraphLabels($photosHoversGraph, $options),
                'total' => $this->getTotalAction($photosHoversGraph)
            ),
            'photosClicksGraph' => array(
                'data' => $photosClicksGraph,
                'labels' => $this->getGraphLabels($photosClicksGraph, $options),
                'total' => $this->getTotalAction($photosClicksGraph)
            ),
            'photosInteractionGraph' => array(
                'data' => $photosInteractionGraph,
                'labels' => $this->getGraphLabels($photosInteractionGraph, $options),
                'total' => $this->getTotalAction($photosInteractionGraph, true)
            ),
            'photosHoversPercentage' => 0,
            'tagsHoversPercentage' => 0,
            'tagsClicksPercentage' => 0,
            'interactionTime' => $this->getAvgInteractionTime($profile, $options),
        );

        // Calculate percentages
        if ($analytics['photosViews'] > 0)
            $analytics['photosHoversPercentage'] = round(($analytics['photosHovers'] / $analytics['photosViews']) * 100);
        if ($analytics['photosHovers'] > 0)
            $analytics['tagsHoversPercentage'] = round(($analytics['tagsHovers'] / $analytics['photosHovers']) * 100);
        if ($analytics['tagsHovers'] > 0)
            $analytics['tagsClicksPercentage'] = round(($analytics['tagsClicks'] / $analytics['tagsHovers']) * 100);

        if (!array_key_exists('daterange', $options))
            $options['daterangeActivity'] = $options['fromDate']->format('m/d/Y').' - '.$options['toDate']->format('m/d/Y');

        return $analytics;
    }

    public function findAnalyticsByPhoto($photo)
    {
        $analytics = array(
            'tagsHovers' => $this->getPhotoTagsCountByAction(Analytic::ACTION_HOVER, $photo),
            'tagsClicks' => $this->getPhotoTagsCountByAction(Analytic::ACTION_CLICK, $photo),
            'photosHoversPercentage' => 0,
            'tagsHoversPercentage' => 0,
            'tagsClicksPercentage' => 0
        );

        // Calculate percentages
        if ($photo->getViewsCount() > 0)
            $analytics['photosHoversPercentage'] = round(($photo->getHoversCount() / $photo->getViewsCount()) * 100);
        if ($photo->getHoversCount() > 0)
            $analytics['tagsHoversPercentage'] = round(($analytics['tagsHovers'] / $photo->getHoversCount()) * 100);
        if ($analytics['tagsHovers'] > 0)
            $analytics['tagsClicksPercentage'] = round(($analytics['tagsClicks'] / $analytics['tagsHovers']) * 100);

        return $analytics;
    }

    public function findSourcesByPhoto($photo, $returnQueryBuilder = true)
    {
        $qb = $this->getEntityManager()->createQuery(
            'SELECT (SELECT COUNT(aa.id) FROM AdEntifyCoreBundle:Analytic aa WHERE aa.sourceUrl = a.sourceUrl) occurences,
              a.sourceUrl url FROM AdEntifyCoreBundle:Analytic a WHERE a.sourceUrl IS NOT NULL AND a.photo = :photo
               GROUP BY a.sourceUrl ORDER BY occurences')
            ->setParameters(array(
                'photo' => $photo->getId()
            ));

        if ($returnQueryBuilder)
            return $qb;

        return $qb->getArrayResult();
    }

    public function findSourcesByProfile($profile)
    {
        $sources = array();
        if (is_a($profile, 'AdEntify\CoreBundle\Entity\Brand')) {
            $sources = $this->createQueryBuilder('a')
                ->select('DISTINCT a.sourceUrl')
                ->leftJoin('a.photo', 'p')
                ->leftJoin('p.tags', 't')
                ->where('a.sourceUrl IS NOT NULL')
                ->andWhere('t.brand = :brand')
                ->setParameters(array(
                    'brand' => $profile->getId()
                ))->getQuery()->getArrayResult();
        } else if (is_a($profile, 'AdEntify\CoreBundle\Entity\User')) {
            $sources = $this->createQueryBuilder('a')
                ->select('DISTINCT a.sourceUrl')
                ->leftJoin('a.photo', 'p')
                ->where('a.sourceUrl IS NOT NULL')
                ->andWhere('p.owner = :user')
                ->setParameters(array(
                    'user' => $profile->getId()
                ))->getQuery()->getArrayResult();
        }

        if (count($sources) > 0) {
            foreach($sources as &$source) {
                $parsedSource = parse_url($source['sourceUrl']);
                if (array_key_exists('host', $parsedSource))
                    $source = $parsedSource['host'];
            }
        }

        return $sources;
    }

    private function getElementCountByAction($profile, $options = array())
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(DISTINCT a.id)')
            ->Where('a.element = :element')
            ->andWhere('a.action = :action');

        // Element
        if ($options['element'] == Analytic::ELEMENT_PHOTO)
            $qb->andWhere($qb->expr()->isNotNull('a.photo'));

        // Restrict analytic to a source
        if (array_key_exists('source', $options)) {
            $qb->andWhere($qb->expr()->like('a.sourceUrl', ':source'));
            $parameters['source'] = '%'.$options['source'].'%';
        }

        if (is_a($profile, 'AdEntify\CoreBundle\Entity\Brand')) {
            $parameters['element'] = $options['element'];
            $parameters['action'] = $options['action'];
            $parameters['brand'] = $profile->getId();

            if ($options['element'] == Analytic::ELEMENT_PHOTO) {
                $qb->leftJoin('a.photo', 'p')
                    ->leftJoin('p.tags', 't')
                    ->andWhere('b = :brand');
            }
            else
                $qb->leftJoin('a.tag', 't')
                    ->andWhere('b = :brand');
            $qb->leftJoin('t.brand', 'b')
                ->setParameters($parameters);
        } else {
            $parameters['element'] = $options['element'];
            $parameters['action'] = $options['action'];
            $parameters['user'] = $profile->getId();

            if ($options['element'] == Analytic::ELEMENT_PHOTO)
                $qb->leftJoin('a.photo', 'p')
                    ->leftJoin('p.owner', 'u');
            else
                $qb->leftJoin('a.tag', 't')
                    ->leftJoin('t.owner', 'u');

            $qb->andWhere('u = :user')
                ->setParameters($parameters);
        }
        if (array_key_exists('daterange', $options)) {
            $dates = explode(' - ', $options['daterange']);
            $from = new \DateTime($dates[0]);
            $to = new \DateTime($dates[1]);

            $qb->andwhere('a.createdAt >= :from')
                ->andWhere('a.createdAt <= :to')
                ->setParameter('from', $from)
                ->setParameter('to', $to);
        }
        if (array_key_exists('graph', $options))
        {
            return $qb->select('COUNT(DISTINCT a.id) as data, DATE_FORMAT(a.createdAt, :sqlDateFormat) as period')
                ->groupBy('period')
                ->setParameter('sqlDateFormat', $options['sqlDateFormat'])
                ->orderBy('a.createdAt')
                ->getQuery()->getScalarResult();
        }
        else
            return $qb->getQuery()->getSingleScalarResult();
    }

    private function parseDataForGraph($data, $options)
    {
        $result = $options['labels'];
        foreach($data as $d) {
            if (array_key_exists($d['period'], $options['labels']))
                $result[$d['period']] = $d['data'];
        }
        return $result;
    }

    private function initializeGraphData($options = array())
    {
        $labels = array();
        $labels[$options['fromDate']->format($options['phpDateFormat'])] = 0;
        $copyFromDate = clone $options['fromDate'];
        do {
            $nextMonth = $copyFromDate->add(new \DateInterval($options['dateInterval']));
            $labels[$nextMonth->format($options['phpDateFormat'])] = 0;
        } while ($nextMonth < $options['toDate']);

        return $labels;
    }

    private function getStatsPeriod(&$options = array())
    {
        $options['sqlDateFormat'] = '%d %M';
        $options['phpDateFormat'] = 'd F';

        if (array_key_exists('daterange', $options)) {
            $dates = explode(' - ', $options['daterange']);
            $options['fromDate'] = new \DateTime($dates[0]);
            $options['toDate'] = new \DateTime($dates[1]);
            $diff = date_diff($options['fromDate'], $options['toDate'])->format('%a');
            if ($diff > 365 * 3) {
                $options['sqlDateFormat'] = '%Y';
                $options['phpDateFormat'] = 'Y';
                $options['dateInterval'] = 'P1Y';
            } else if ($diff > 90) {
                $options['sqlDateFormat'] = '%M %Y';
                $options['phpDateFormat'] = 'F Y';
                $options['dateInterval'] = 'P1M';
            } else {
                $options['sqlDateFormat'] = '%d %M';
                $options['phpDateFormat'] = 'd F';
                $options['dateInterval'] = 'P1D';
            }
        } else {
            $options['toDate'] = new \DateTime();
            $options['fromDate'] = (new \DateTime())->sub(new \DateInterval('P1M'));
            $options['dateInterval'] = 'P1D';
        }
        $options['labels'] = $this->initializeGraphData($options);
        return $options;
    }

    private function getPhotoTagsCountByAction($action, $photo)
    {
        return $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.photo = :photo')
            ->andWhere('a.element = :element')
            ->andWhere('a.action = :action')
            ->setParameters(array(
                'element' => Analytic::ELEMENT_TAG,
                'action' => $action,
                'photo' => $photo
            ))
            ->getQuery()->getSingleScalarResult();
    }

    private function getAvgInteractionTime($profile, $options)
    {
        $parameters = array(
            'interaction' => Analytic::ACTION_INTERACTION,
            'profile' => $profile->getId(),
        );

        $qb = $this->createQueryBuilder('a')
            ->select('AVG(a.actionValue)/1000 as data, DATE_FORMAT(a.createdAt, :sqlDateFormat) as period')
            ->where('a.action = :interaction');

        // Restrict analytic to a source
        if (array_key_exists('source', $options)) {
            $qb->andWhere($qb->expr()->like('a.sourceUrl', ':source'));
            $parameters['source'] = '%'.$options['source'].'%';
        }

        if (is_a($profile, 'AdEntify\CoreBundle\Entity\Brand')) {
            $qb->leftJoin('a.photo', 'p')
                ->leftJoin('p.tags', 't')
                ->leftJoin('t.brand', 'b')
                ->andWhere('b = :profile')
                ->setParameters($parameters);

        } else {
            $qb->leftJoin('a.photo', 'p')
                ->leftJoin('p.owner', 'u')
                ->andWhere('u = :profile')
                ->setParameters($parameters);
        }
        return $qb->groupBy('period')
            ->setParameter('sqlDateFormat', $options['sqlDateFormat'])
            ->getQuery()->getScalarResult();
    }
}