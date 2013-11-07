<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 18/04/2013
 * Time: 17:57
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\Person;
use AdEntify\CoreBundle\Form\PersonType;
use Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class PeopleController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("Person")
 */
class PeopleController extends FosRestController
{
    /**
     * @View()
     */
    public function cgetAction()
    {
        return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Person')->findAll();
    }

    /**
     * @View()
     */
    public function getAction($id)
    {
        return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Person')->find($id);
    }

    /**
     * @View()
     */
    public function postAction(Request $request)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            // Check if existing user exist with person facebookId
            if ($request->request->has('person')) {
                $personRequest = $request->request->get('person');
                $user = $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:User')->findOneBy(array(
                    'facebookId' => $personRequest['facebookId']
                ));
            }
            $person = new Person();
            $form = $this->getForm($person);
            $form->bind($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                if (isset($user)) {
                    $person->setUser($user);
                }
                $em->persist($person);
                $em->flush();

                return $person;
            } else {
                return $form;
            }
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * Get form for Venue
     *
     * @param null $venue
     * @return mixed
     */
    protected function getForm($person = null)
    {
        return $this->createForm(new PersonType(), $person);
    }
}