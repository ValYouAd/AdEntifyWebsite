<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 23/12/14
 * Time: 13:19
 */

namespace AdEntify\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\HttpException;
use JMS\SecurityExtraBundle\Annotation\Secure;
class BlockController extends Controller
{
    /**
     * @Template()
     *
     * @return array
     * @throws HttpException
     */
    public function changeUserAction($currentProfile, $currentProfileType)
    {
        if ($this->getUser()) {
            $accounts = array();
            if ($this->getUser()->getBrand()) {
                $accounts['accounts.brands'] = array(array(
                    'link' => $this->generateUrl('dashboard_stats', array(
                        'brand' => $this->getUser()->getBrand()->getSlug()
                    ), true),
                    'type' => 'brand',
                    'selected' => $currentProfileType == 'brand' && $currentProfile->getId() == $this->getUser()->getBrand()->getId() ? true : false,
                    'name' => $this->getUser()->getBrand()->getName()
                ));
            }

            $accounts['accounts.users'] = array(array(
                'link' => $this->generateUrl('dashboard_stats', array(
                    'user' => $this->getUser()->getId()
                ), true),
                'type' => 'user',
                'selected' => $currentProfileType == 'user' && $currentProfile->getId() == $this->getUser()->getId() ? true : false,
                'name' => $this->getUser()->getFullname()
            ));

            return array(
                'accounts' => $accounts
            );
        } else
            throw new HttpException(403);
    }
} 