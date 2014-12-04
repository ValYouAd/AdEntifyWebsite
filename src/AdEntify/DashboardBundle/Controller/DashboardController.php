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
     * @Route("{_locale}/app/my/dashboard/analytics/{page}",
     *  defaults={"_locale" = "en", "page" = "1"},
     *  requirements={"_locale" = "en|fr", "page" = "\d+"},
     *  name="dashboard_stats")
     * @Template()
     */
    public function analyticsAction($page = 1)
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
            $result['photos'] = $em->getRepository('AdEntifyCoreBundle:Photo')->getPhotos($this->getUser(), $page);
            return array(
                'analytics' => $result,
                'brand' => $this->getUser()->getBrand(),
                'user' => $this->getUser(),
                'globalAnalytics' => $analyticRepository->findGlobalAnalyticsByUser($this->getUser()),
            );
        } else
            throw new HttpException(403);
    }

    /**
     * @Route("{_locale}/app/my/dashboard/analytics/details/{photo}/{page}",
     *  defaults={"_locale" = "en", "page" = "1"},
     *  requirements={"_locale" = "en|fr", "photo" = "\d+", "page" = "\d+"},
     *  name="dashboard_details")
     * @Template()
     */
    public function detailsAction($photo, $page = 1)
    {
        if ($this->getUser())
        {
            $tagRepository = $this->getDoctrine()->getRepository('AdEntifyCoreBundle:Tag');

            return array(
                    'photo' => $this->getDoctrine()->getRepository('AdEntifyCoreBundle:Photo')->find($photo),
                    'tags' => $tagRepository->findTagsByPhoto($photo, $page),
                    'nbTaggers' => $tagRepository->getTaggersCountByPhoto($photo),
                    'photoId' => $photo
                );
        }
        else
            throw new HttpException(403);
    }
}