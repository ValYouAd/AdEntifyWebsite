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
            $owners = array();
            $photos = array();

            $em = $this->getDoctrine()->getManager();
            $analyticRepository = $em->getRepository('AdEntifyCoreBundle:Analytic');

            if ($this->getUser()->getBrand())
            {
                $brandTags = $em->getRepository('AdEntifyCoreBundle:Tag')->findBy(array(
                    'brand' => $this->getUser()->getBrand()
                ));
                if (!empty($brandTags))
                {
                    foreach ($brandTags as $brandTag)
                    {
                        $owners[] = $brandTag->getOwner()->getId();
                        $photos[] = $brandTag->getPhoto()->getId();
                    }
                    $result['nbTagged'] = count($brandTags);
                    $result['nbUsers'] = count(array_unique($owners));
                    $result['nbPhotos'] = count(array_unique($photos));
                }
            }
            return array(
                'analytics' => $result,
                'globalAnalytics' => $analyticRepository->findGlobalAnalyticsByUser($this->getUser())
            );
        }
        else
            throw new HttpException(403);
    }
}
