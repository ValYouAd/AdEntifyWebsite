<?php

namespace AdEntify\BackofficeBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AdEntify\CoreBundle\Entity\Legal;
use AdEntify\CoreBundle\Form\LegalType;

/**
 * Legal controller.
 *
 * @Route("/legals")
 */
class LegalController extends Controller
{

    /**
     * Lists all Legal entities.
     *
     * @Route("/", name="legals")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $delete_forms = array();

        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AdEntifyCoreBundle:Legal')->findAll();

        foreach ($entities as $entity) {
            $delete_forms[$entity->getId()] = $this->createDeleteForm($entity->getId())->createView();
        }

        return array(
            'entities' => $entities,
            'delete_forms' => $delete_forms,
        );
    }
    /**
     * Creates a new Legal entity.
     *
     * @Route("/", name="legals_create")
     * @Method("POST")
     * @Template("AdEntifyBackofficeBundle:Legal:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Legal();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('legals_show', array('id' => $entity->getId())));
        }
        else {
            echo $form->getErrorsAsString();
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Legal entity.
     *
     * @param Legal $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Legal $entity)
    {
        $form = $this->createForm(new LegalType(), $entity, array(
            'action' => $this->generateUrl('legals_create'),
            'method' => 'POST',
            'attr' => array('style' => 'height: 100%'),
        ));

        $form->add('submit', 'submit', array('label' => 'Create', 'attr' => array('class' => 'btn btn-primary')));

        return $form;
    }

    /**
     * Displays a form to create a new Legal entity.
     *
     * @Route("/new", name="legals_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Legal();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Legal entity.
     *
     * @Route("/{id}", name="legals_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AdEntifyCoreBundle:Legal')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Legal entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Legal entity.
     *
     * @Route("/{id}/edit", name="legals_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AdEntifyCoreBundle:Legal')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Legal entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Legal entity.
    *
    * @param Legal $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Legal $entity)
    {
        $form = $this->createForm(new LegalType(), $entity, array(
            'action' => $this->generateUrl('legals_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Legal entity.
     *
     * @Route("/{id}", name="legals_update")
     * @Method("PUT")
     * @Template("AdEntifyBackofficeBundle:Legal:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AdEntifyCoreBundle:Legal')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Legal entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('legals_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Legal entity.
     *
     * @Route("/{id}", name="legals_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AdEntifyCoreBundle:Legal')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Legal entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('legals'));
    }

    /**
     * Creates a form to delete a Legal entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('legals_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete', 'attr' => array('class' => 'btn btn-danger')))
            ->getForm()
        ;
    }
}
