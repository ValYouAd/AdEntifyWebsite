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
                $sinceDate = $sinceDate->sub(new \DateInterval('PT2S'));
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

    private function parseDataForGraph($data, $options)
    {
        $result = $options['defaultLabels'];
        foreach($data as $d) {
            $result[$d['period']] = $d['data'];
        }

        return $result;
    }

    private function fillGraphLabelsArray($data = array())
    {
        return array_keys($data);
    }

    private function getStatsPeriod(&$options = array())
    {
        $options['sqlDateFormat'] = '%M %Y';
        $options['phpDateFormat'] = 'F Y';

        if (array_key_exists('daterange', $options)) {
            $dates = explode(' - ', $options['daterange']);
            $from = new \DateTime($dates[0]);
            $to = new \DateTime($dates[1]);
            $diff = date_diff($from, $to)->format('%a');
            if ($diff > 365 * 3) {
                $options['sqlDateFormat'] = 'YEAR';
                $options['graphRange'] = intval($diff / 365); //TODO
            } else if ($diff > 120) {
                $options['sqlDateFormat'] = 'MONTH';
                $options['graphRange'] = intval($diff / 30);
            } else {
                $options['sqlDateFormat'] = 'DAY'; //TODO
                $options['graphRange'] = $diff;
            }
        } else {
            $options['toDate'] = new \DateTime();
            $options['fromDate'] = (new \DateTime())->sub(new \DateInterval('P6M'));

            $labels = array();
            $labels[$options['fromDate']->format($options['phpDateFormat'])] = 0;
            do {
                $nextMonth = $options['fromDate']->add(new \DateInterval('P1M'));
                $labels[$nextMonth->format($options['phpDateFormat'])] = 0;
            } while ($nextMonth < $options['toDate']);

            $options['defaultLabels'] = $labels;
        }

        return $options;
    }

    public function findGlobalAnalyticsByUser(User $user, $options = array())
    {
        $this->getStatsPeriod($options);
        $photosViewsGraph = $this->parseDataForGraph($this->getElementCountByAction($user, array_merge(array(
            'element' => Analytic::ELEMENT_PHOTO,
            'action' => Analytic::ACTION_VIEW,
            'graph' => true
        ), $options)), $options);

        $analytics = array(
            'photosViews' => $this->getElementCountByAction($user, array(
                'element' => Analytic::ELEMENT_PHOTO,
                'action' => Analytic::ACTION_VIEW
            )),
            'photosHovers' => $this->getElementCountByAction($user, array(
                'element' => Analytic::ELEMENT_PHOTO,
                'action' => Analytic::ACTION_HOVER
            )),
            'tagsHovers' => $this->getElementCountByAction($user, array(
                'element' => Analytic::ELEMENT_TAG,
                'action' => Analytic::ACTION_HOVER
            )),
            'tagsClicks' => $this->getElementCountByAction($user, array(
                'element' => Analytic::ELEMENT_TAG,
                'action' => Analytic::ACTION_CLICK
            )),
            'photosViewsGraph' => array(
                'data' => $photosViewsGraph,
                'labels' => $this->fillGraphLabelsArray($photosViewsGraph)
            ),
            'photosHoversGraph' => $this->parseDataForGraph($this->getElementCountByAction($user, array_merge(array(
                'element' => Analytic::ELEMENT_PHOTO,
                'action' => Analytic::ACTION_HOVER,
                'graph' => true
            ), $options)), $options),
            'photosHoversPercentage' => 0,
            'tagsHoversPercentage' => 0,
            'tagsClicksPercentage' => 0,
            'interactionTime' => $this->getAvgInteractionTime($user),
        );
//        echo "<pre>";
//        print_r($analytics);die;
        // Calculate percentages
        if ($analytics['photosViews'] > 0)
            $analytics['photosHoversPercentage'] = ($analytics['photosHovers'] / $analytics['photosViews']) * 100;
        if ($analytics['photosHovers'] > 0)
            $analytics['tagsHoversPercentage'] = ($analytics['tagsHovers'] / $analytics['photosHovers']) * 100;
        if ($analytics['tagsHovers'] > 0)
            $analytics['tagsClicksPercentage'] = ($analytics['tagsClicks'] / $analytics['tagsHovers']) * 100;

        return $analytics;
    }

    private function getElementCountByAction(User $user, $options = array())
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(DISTINCT a.id)')
            ->Where('a.element = :element')
            ->andWhere('a.action = :action');
        if ($options['element'] == Analytic::ELEMENT_PHOTO)
            $qb->andWhere($qb->expr()->isNotNull('a.photo'));

        if ($user->getBrand()) {
            $parameters['element'] = $options['element'];
            $parameters['action'] = $options['action'];
            $parameters['brand'] = $user->getBrand()->getId();

            if ($options['element'] == Analytic::ELEMENT_PHOTO) {
                $parameters['user'] = $user;

                $qb->leftJoin('a.photo', 'p')
                    ->leftJoin('p.tags', 't')
                    ->andWhere('b = :brand OR p.owner = :user');
            }
            else
                $qb->leftJoin('a.tag', 't')
                    ->andWhere('b = :brand');
            $qb->leftJoin('t.brand', 'b')
                ->setParameters($parameters);
        } else {
            if ($options['element'] == Analytic::ELEMENT_PHOTO)
                $qb->leftJoin('a.photo', 'p')
                    ->leftJoin('p.owner', 'u');
            else
                $qb->leftJoin('a.tag', 't')
                    ->leftJoin('t.owner', 'u');

            $qb->andWhere('u = :user')
                ->setParameters(array(
                    'element' => $options['element'],
                    'action' => $options['action'],
                    'user' => $user->getId()
                ));
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
            $analytics['photosHoversPercentage'] = ($photo->getHoversCount() / $photo->getViewsCount()) * 100;
        if ($photo->getHoversCount() > 0)
            $analytics['tagsHoversPercentage'] = ($analytics['tagsHovers'] / $photo->getHoversCount()) * 100;
        if ($analytics['tagsHovers'] > 0)
            $analytics['tagsClicksPercentage'] = ($analytics['tagsClicks'] / $analytics['tagsHovers']) * 100;

        return $analytics;
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

    private function getAvgInteractionTime($user)
    {
        $qb = $this->createQueryBuilder('a')
            ->select('AVG(a.actionValue)')
            ->where('a.action = :interaction');

        if ($user->getBrand()) {
            return $qb
                ->leftJoin('a.photo', 'p')
                ->leftJoin('p.tags', 't')
                ->leftJoin('t.brand', 'b')
                ->andWhere('b = :brand')
                ->setParameters(array(
                    'interaction' => Analytic::ACTION_INTERACTION,
                    'brand' => $user->getBrand()->getId(),
                ))
                ->getQuery()->getSingleScalarResult();
        } else {
            return $qb
                ->leftJoin('a.photo', 'p')
                ->leftJoin('p.owner', 'u')
                ->andWhere('u = :user')
                ->setParameters(array(
                    'interaction' => Analytic::ACTION_INTERACTION,
                    'user' => $user->getId()
                ))
                ->getQuery()->getSingleScalarResult();
        }
    }
}