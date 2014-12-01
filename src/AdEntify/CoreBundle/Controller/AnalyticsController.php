<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 07/11/14
 * Time: 14:59
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\Analytic;
use AdEntify\CoreBundle\Entity\TagStats;
use AdEntify\CoreBundle\Form\AnalyticType;
use AdEntify\CoreBundle\Form\TagType;
use Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection;

use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class AnalyticsController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("Analytics")
 */
class AnalyticsController extends FosRestController
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Post an analytic",
     *  input="AdEntify\CoreBundle\Form\AnalyticType",
     *  output="AdEntify\CoreBundle\Entity\Analytic",
     *  statusCodes={
     *      200="Returned if the analytic is created",
     *  },
     *  section="Analytics"
     * )
     *
     * @View(serializerGroups={"details"})
     */
    public function postAction(Request $request)
    {
        // Check if its a bot
        if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider/i', $_SERVER['HTTP_USER_AGENT']))
            throw new HttpException('403', 'Bot or spider');

        $em = $this->getDoctrine()->getManager();
        $analytic = new Analytic();
        $form = $this->getForm($analytic);
        $form->bind($request);
        if ($form->isValid()) {
            $analytic->setIpAddress($request->getClientIp());
            if ($this->getUser())
                $analytic->setUser($this->getUser());

            if (!$em->getRepository('AdEntifyCoreBundle:Analytic')->isAlreadyTracked($analytic)) {
                $em->persist($analytic);
                $em->flush();

                return $analytic;
            } else
                throw new HttpException('403', 'Already tracked');
        } else {
            return $form;
        }
    }

    /**
     * Get form for Analytic
     *
     * @param null $analytic
     * @return mixed
     */
    protected function getForm(Analytic $analytic = null)
    {
        $options = array();
        if ($this->getRequest()->isMethod('POST')) {
            if ($analytic->getPhoto())
                $options['photoId'] = $this->getRequest()->get('analytic')['photo'];
            if ($analytic->getUser())
                $options['userId'] = $this->getRequest()->get('analytic')['user'];
            if ($analytic->getTag())
                $options['tagId'] = $this->getRequest()->get('analytic')['tag'];
        }

        return $this->createForm(new AnalyticType(), $analytic, $options);
    }
}