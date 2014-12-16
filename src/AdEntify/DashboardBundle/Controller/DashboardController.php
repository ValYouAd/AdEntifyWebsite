<?php
/**
 * Created by PhpStorm.
 * User: huas
 * Date: 01/12/2014
 * Time: 15:38
 */

namespace AdEntify\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\HttpException;


class DashboardController extends Controller
{

    /**
     * @Route("{_locale}/app/my/dashboard/analytics",
     *  defaults={"_locale" = "en"},
     *  requirements={"_locale" = "en|fr"},
     *  name="dashboard_stats")
     * @Template()
     */
    public function analyticsAction()
    {
        if ($this->getUser()) {
            $result = array(
                'nbTagged' => 0,
                'nbUsers' => 0,
                'nbPhotos' => 0
            );
            $em = $this->getDoctrine()->getManager();
            $tagRepository = $em->getRepository('AdEntifyCoreBundle:Tag');
            $analyticRepository = $em->getRepository('AdEntifyCoreBundle:Analytic');

            $result['nbTagged'] = $tagRepository->countBySelector($this->getUser(), 'id');
            $result['nbUsers'] = $tagRepository->countBySelector($this->getUser(), 'owner', 'DISTINCT');
            $result['nbPhotos'] = $tagRepository->countBySelector($this->getUser(), 'photo', 'DISTINCT');

            $this->get('session')->set('dashboardPage', (array_key_exists('page', $_GET)) ? $_GET['page'] : 1);

            $options = array();
            if ($this->getRequest()->query->has('daterange')) {
                $options['daterange'] = $this->getRequest()->query->get('daterange');
            }

            $pagination = $this->get('knp_paginator')->paginate(
                $em->getRepository('AdEntifyCoreBundle:Photo')->getPhotos($this->getUser(), $options),
                $this->get('request')->query->get('page', 1),
                $this->container->getParameter('analytics')['nb_elements_by_page']
            );

            return array(
                'analytics' => $result,
                'brand' => $this->getUser()->getBrand(),
                'user' => $this->getUser(),
                'globalAnalytics' => $analyticRepository->findGlobalAnalyticsByUser($this->getUser()),
                'pagination' => $pagination,
                'daterange' => array_key_exists('daterange', $options) ? $options['daterange'] : null
            );
        } else
            throw new HttpException(403);
    }

    /**
     * @Route("{_locale}/app/my/dashboard/analytics/details/{photoId}",
     *  defaults={"_locale" = "en"},
     *  requirements={"_locale" = "en|fr", "photoId" = "\d+"},
     *  name="dashboard_details")
     *
     * @param integer $photoId
     * @return array
     *
     * @Template()
     */
    public function detailsAction($photoId)
    {
        if ($this->getUser())
        {
            $tagRepository = $this->getDoctrine()->getRepository('AdEntifyCoreBundle:Tag');
            $photo = $this->getDoctrine()->getRepository('AdEntifyCoreBundle:Photo')->find($photoId);

            $pagination = $this->get('knp_paginator')->paginate(
                $tagRepository->findTagsByPhoto($photo),
                $this->get('request')->query->get('page', 1),
                $this->container->getParameter('analytics')['nb_elements_by_page'],
                array(
                    'wrap-queries' => true
                )
            );

            return array(
                'photo' => $photo,
                'pagination' => $pagination,
                'nbTaggers' => $tagRepository->getTaggersCountByPhoto($photo),
                'photoId' => $photoId,
                'page' => $this->get('session')->get('dashboardPage')
            );
        }
        else
            throw new HttpException(403);
    }
}