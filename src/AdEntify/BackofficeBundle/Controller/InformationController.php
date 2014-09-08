<?php

namespace AdEntify\BackofficeBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AdEntify\CoreBundle\Entity\Information;
use AdEntify\BackofficeBundle\Form\InformationType;

/**
 * Information controller.
 *
 * @Route("/informations")
 */
class InformationController extends Controller
{

    /**
     * Lists all information entities.
     *
     * @Route("/", name="informations")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $delete_forms = array();

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AdEntifyCoreBundle:Information')->findAll();

        foreach ($entities as $entity) {
            $delete_forms[$entity->getId()] = $this->createDeleteForm($entity->getId())->createView();
        }

        return array(
            'entities' => $entities,
            'delete_forms' => $delete_forms,
        );
    }
    /**
     * Creates a new Information entity.
     *
     * @Route("/", name="informations_create")
     * @Method("POST")
     * @Template("AdEntifyBackofficeBundle:Information:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Information();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('informations_show', array('id' => $entity->getId())));
        }
        else {
            echo $form->getErrorsAsString();
        }

        return array(
            'entity' => $entity,
            'trans_form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Information entity.
     *
     * @param Information $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Information $entity)
    {
        $form = $this->createForm(new InformationType(), $entity, array(
            'action' => $this->generateUrl('informations_create'),
            'method' => 'POST',
            'attr' => array('style' => 'height: 100%'),
        ));

        $form->add('submit', 'submit', array('label' => 'Create', 'attr' => array('class' => 'btn btn-primary')));

        return $form;
    }

    /**
     * Displays a form to create a new Information entity.
     *
     * @Route("/new", name="informations_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Information();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'trans_form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Information entity.
     *
     * @Route("/{id}", name="informations_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AdEntifyCoreBundle:Information')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find information entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        $query = $em->createQuery(
            'SELECT it.content, it.locale
             FROM AdEntify\CoreBundle\Entity\Informationtranslation it
             WHERE it.object = :id'
        )->setParameter('id', $entity->getId());

        return array(
            'entity'        => $entity,
            'translations'  => $query->getResult(),
            'delete_form'   => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Information entity.
     *
     * @Route("/{id}/edit", name="informations_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AdEntifyCoreBundle:Information')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Information entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        $query = $em->createQuery(
            'SELECT it.content, it.locale
             FROM AdEntify\CoreBundle\Entity\Informationtranslation it
             WHERE it.object = :id'
        )->setParameter('id', $entity->getId());

        return array(
            'entity'      => $entity,
            'translations'  => $query->getResult(),
            'edit_trans_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Information entity.
    *
    * @param Information $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Information $entity)
    {
        $form = $this->createForm(new InformationType(), $entity, array(
            'action' => $this->generateUrl('informations_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Information entity.
     *
     * @Route("/{id}", name="informations_update")
     * @Method("PUT")
     * @Template("AdEntifyBackofficeBundle:Information:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AdEntifyCoreBundle:Information')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find information entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('informations_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_trans_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Information entity.
     *
     * @Route("/{id}", name="informations_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AdEntifyCoreBundle:Information')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Information entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('informations'));
    }

    /**
     * Creates a form to delete a Information entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('informations_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('delete', 'submit', array('label' => 'Delete', 'attr' => array('class' => 'btn btn-danger')))
            ->getForm()
        ;
    }
}
