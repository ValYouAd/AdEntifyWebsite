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
     * @Route("{_locale}/app/my/dashboard/stats", defaults={"_locale" = "en"}, requirements={"_locale" = "en|fr"}, name="dashboard_stats")
     * @Template()
     */
    public function dashboardAction()
    {
        if ($this->getUser())
        {
            $analytics = $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Analytic')->findBy(array(
                'user' => $this->getUser()->getId()
            ));
            return $analytics;
        }
        else
            throw new HttpException(403);
    }
}
