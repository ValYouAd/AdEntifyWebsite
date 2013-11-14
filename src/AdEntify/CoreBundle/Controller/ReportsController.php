<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 14/11/2013
 * Time: 18:37
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\Photo;
use AdEntify\CoreBundle\Entity\Report;
use AdEntify\CoreBundle\Form\ReportType;
use AdEntify\CoreBundle\Util\PaginationTools;
use AdEntify\CoreBundle\Util\UserCacheManager;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection;

use AdEntify\CoreBundle\Entity\Action;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class ReportsController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("Report")
 */
class ReportsController extends FosRestController
{
    /**
     * @param Request $request
     * @return Report|\Symfony\Component\Form\Form|\Symfony\Component\Form\FormError[]
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     *
     * @View()
     */
    public function postAction(Request $request)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $report = new Report();
            $form = $this->getForm($report);
            $form->bind($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $user = $securityContext->getToken()->getUser();
                $report->setUser($user);
                $em->persist($report);
                $em->flush();

                return $report;
            } else {
                return $form;
            }
        } else {
            throw new HttpException(401);
        }
    }

    private function getForm(Report $report = null) {
        return $this->createForm(new ReportType(), $report);
    }
}