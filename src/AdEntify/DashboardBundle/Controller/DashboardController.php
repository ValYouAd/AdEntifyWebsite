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
use JMS\SecurityExtraBundle\Annotation\Secure;
use AdEntify\CoreBundle\Entity\AnalyticRepository;


class DashboardController extends Controller
{
    /**
     * @Route("{_locale}/app/my/dashboard/analytics",
     *  defaults={"_locale" = "en"},
     *  requirements={"_locale" = "en|fr"},
     *  name="dashboard_stats")
     * @Template()
     * @Secure("ROLE_USER, ROLE_FACEBOOK, ROLE_TWITTER")
     */
    public function analyticsAction()
    {
        if ($this->getUser()) {
            if ($this->getRequest()->query->has('brand')
                && (!$this->getUser()->getBrand() || $this->getUser()->getBrand()->getSlug() != $this->getRequest()->query->get('brand'))) {
                throw new HttpException(403);
            } else if ($this->getRequest()->query->has('user') && ($this->getUser()->getId() != $this->getRequest()->query->get('user'))) {
                throw new HttpException(403);
            }
            if ($this->getRequest()->query->has('brand')) {
                $profile = $this->getUser()->getBrand();
                $currentProfileType = 'brand';
            } else {
                $profile = $this->getUser();
                $currentProfileType = 'user';
            }

            $result = array(
                'nbTagged' => 0,
                'nbUsers' => 0,
                'nbPhotos' => 0
            );
            $em = $this->getDoctrine()->getManager();
            $tagRepository = $em->getRepository('AdEntifyCoreBundle:Tag');
            $analyticRepository = $em->getRepository('AdEntifyCoreBundle:Analytic');

            $result['nbTagged'] = $tagRepository->countBySelector($profile, 'id');
            $result['nbUsers'] = $tagRepository->countBySelector($profile, 'owner', 'DISTINCT');
            $result['nbPhotos'] = $tagRepository->countBySelector($profile, 'photo', 'DISTINCT');

            $this->get('session')->set('dashboardPage', (array_key_exists('page', $_GET)) ? $_GET['page'] : 1);

            $options = array();
            if ($this->getRequest()->query->has('daterange') && $this->getRequest()->query->get('daterange')) {
                $options['daterange'] = $this->getRequest()->query->get('daterange');
            }
            if ($this->getRequest()->query->has('source')) {
                $options['source'] = $this->getRequest()->query->get('source');
            }

            $pagination = $this->get('knp_paginator')->paginate(
                $em->getRepository('AdEntifyCoreBundle:Photo')->getPhotos($profile, $options),
                $this->get('request')->query->get('page', 1),
                $this->container->getParameter('analytics')['nb_elements_by_page']
            );

            return array(
                'analytics' => $result,
                'currentProfile' => $profile,
                'currentProfileType' => $currentProfileType,
                'brand' => $this->getUser()->getBrand(),
                'user' => $this->getUser(),
                'globalAnalytics' => $analyticRepository->findGlobalAnalyticsByUser($profile, $options),
                'pagination' => $pagination,
                'daterange' => array_key_exists('daterange', $options) ? $options['daterange'] : null,
                'daterangeActivity' => array_key_exists('daterangeActivity', $options) ? $options['daterangeActivity'] : null,
                'sources' => $analyticRepository->findSourcesByProfile($profile),
                'currentSource' => $this->getRequest()->query->has('source') ? $this->getRequest()->query->get('source') : null
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
     * @Secure("ROLE_USER, ROLE_FACEBOOK, ROLE_TWITTER")
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
                'sources' => $this->getDoctrine()->getRepository('AdEntifyCoreBundle:Analytic')->findSourcesByPhoto($photo, false),
                'nbTaggers' => $tagRepository->getTaggersCountByPhoto($photo),
                'photoId' => $photoId,
                'page' => $this->get('session')->get('dashboardPage')
            );
        }
        else
            throw new HttpException(403);
    }
}