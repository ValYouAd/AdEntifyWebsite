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
     * @Route("{_locale}/app/my/dashboard/analytics", defaults={"_locale" = "en"}, requirements={"_locale" = "en|fr"}, name="dashboard_stats")
     * @Template()
     */
    public function analyticsAction()
    {
        if ($this->getUser())
        {
            $result = array(
                'nbTagged' => 0,
                'nbUsers' => 0,
                'nbPhotos' => 0
            );
            $em = $this->getDoctrine()->getManager();
            $analyticRepository = $em->getRepository('AdEntifyCoreBundle:Analytic');

            $result['nbTagged'] = $em->getRepository('AdEntifyCoreBundle:Tag')->countBySelector($this->getUser(), 'id');
            $result['nbUsers'] = $em->getRepository('AdEntifyCoreBundle:Tag')->countBySelector($this->getUser(), 'owner', 'DISTINCT');
            $result['nbPhotos'] = $em->getRepository('AdEntifyCoreBundle:Tag')->countBySelector($this->getUser(), 'photo', 'DISTINCT');
            return array(
                    'analytics' => $result,
                    'isBrand' => ($this->getUser()->getBrand()) ? true : false,
                    'globalAnalytics' => $analyticRepository->findGlobalAnalyticsByUser($this->getUser())
                );
        }
        else
            throw new HttpException(403);
    }
}
