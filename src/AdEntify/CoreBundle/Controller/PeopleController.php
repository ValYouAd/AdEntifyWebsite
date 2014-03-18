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
use AdEntify\CoreBundle\Entity\User;
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
            if (!$request->request->has('person'))
                throw new HttpException(404);

            $em = $this->getDoctrine()->getManager();

            $personRequest = $request->request->get('person');
            $user = null;
            $person = null;
            if (array_key_exists('facebookId', $personRequest)) {
                $user = $em->getRepository('AdEntifyCoreBundle:User')->findOneBy(array(
                    'facebookId' => $personRequest['facebookId']
                ));
                $person = $em->getRepository('AdEntifyCoreBundle:Person')->findOneBy(array(
                    'facebookId' => $personRequest['facebookId']
                ));
            }

            if ($person) {
                if (!$person->getUser() && $user)
                    $person->setUser($user);

                $em->merge($person);
                $em->flush();

                return $person;
            } else {
                $person = new Person();
                $form = $this->getForm($person);
                $form->bind($request);
                if ($form->isValid()) {
                    $em = $this->getDoctrine()->getManager();
                    if ($user) {
                        $person->setUser($user);
                    }
                    if (!$person->getGender()) {
                        $person->setGender(User::GENDER_UNKNOWN);
                    }
                    $em->persist($person);
                    $em->flush();
                    return $person;
                } else {
                    return $form;
                }
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