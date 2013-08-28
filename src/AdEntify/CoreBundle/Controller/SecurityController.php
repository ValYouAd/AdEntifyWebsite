<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 27/05/2013
 * Time: 11:59
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Model\CsrfToken;
use Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection;

/**
 * Class SecurityController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 */
class SecurityController  extends FosRestController
{
    /**
     * @View()
     */
    public function getCsrftokenAction($intention)
    {
        return $this->container->get('form.csrf_provider')->generateCsrfToken($intention);
    }
}