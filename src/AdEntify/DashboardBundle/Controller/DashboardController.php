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
            $isBrand = false;
            $em = $this->getDoctrine()->getManager();
            $analytics = $em->getRepository('AdEntifyCoreBundle:Analytic')->findBy(array(
                'user' => $this->getUser()->getId()
            ));
            if ($this->getUser()->getBrand())
            {
                $isBrand = true;
                $result['nbTagged'] = $em->getRepository('AdEntifyCoreBundle:Tag')->countBrandTags($this->getUser()->getBrand());
                $result['nbUsers'] = $em->getRepository('AdEntifyCoreBundle:Tag')->countBrandTaggers($this->getUser()->getBrand());
                $result['nbPhotos'] = $em->getRepository('AdEntifyCoreBundle:Tag')->countBrandPhotos($this->getUser()->getBrand());
            }
            return array(
                    'analytics' => $result,
                    'isBrand' => $isBrand
                );
        }
        else
            throw new HttpException(403);
    }
}
