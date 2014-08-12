<?php

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\Action;
use AdEntify\CoreBundle\Entity\Photo;
use AdEntify\CoreBundle\Form\BrandType;
use AdEntify\CoreBundle\Model\Thumb;
use AdEntify\CoreBundle\Util\FileTools;
use AdEntify\CoreBundle\Util\PaginationTools;
use AdEntify\CoreBundle\Util\UserCacheManager;
use Symfony\Component\Form\FormError;
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

use AdEntify\CoreBundle\Entity\Brand;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * Class LegalsController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("legals")
 */
class LegalsController extends FOSRestController {

    /**
     * Get a collection of all eula
     *
     * @return ArrayCollection
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Get a collection of eula",
     *  output="AdEntify\CoreBundle\Entity\Legal",
     *  section="E.U.L.A"
     * )
     *
     * @View()
     * @QueryParam(name="orderBy", default="null")
     * @QueryParam(name="way", requirements="DESC|ASC", default="ASC")
     */
    public function getAction()
    {
        return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Legal')->findAll();
    }
}