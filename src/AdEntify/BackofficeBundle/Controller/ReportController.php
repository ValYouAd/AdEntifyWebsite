<?php

namespace AdEntify\BackofficeBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AdEntify\CoreBundle\Entity\Report;
use Symfony\Component\HttpFoundation\Request;

/**
 * Report controller.
 *
 * @Route("/reports")
 */
class ReportController extends Controller
{

    /**
     * Lists all Report entities.
     *
     * @Route("/", name="report")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('AdEntifyCoreBundle:Report')->findAll();

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Finds and displays a Report entity.
     *
     * @Route("/{id}", name="report_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AdEntifyCoreBundle:Report')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Report entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $deletePhotoForm = $entity->getPhoto() ? $this->createDeletePhotoForm($id) : null;
        $deleteTagForm = $entity->getTag() ? $this->createDeleteTagForm($id) : null;

        return array(
            'entity'      => $entity,
            'delete_photo_form' => $deletePhotoForm ? $deletePhotoForm->createView() : null,
            'delete_tag_form' => $deleteTagForm ? $deleteTagForm->createView() : null,
            'delete_form' => $deleteForm->createView()
        );
    }

    /**
     * Deletes a photo entity.
     *
     * @Route("/{id}", name="report_photo_delete")
     * @Method("DELETE")
     */
    public function deletePhotoAction(Request $request, $id)
    {
        $form = $this->createDeletePhotoForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AdEntifyCoreBundle:Report')->find($id);

            if (!$entity && !$entity->getPhoto()) {
                throw $this->createNotFoundException('Unable to find report entity.');
            }

            $entity->getPhoto()->setDeletedAt(new \DateTime());
            $em->merge($entity->getPhoto());
            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('report'));
    }

    /**
     * Deletes a tag entity.
     *
     * @Route("/{id}", name="report_tag_delete")
     * @Method("DELETE")
     */
    public function deleteTagAction(Request $request, $id)
    {
        $form = $this->createDeleteTagForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AdEntifyCoreBundle:Report')->find($id);

            if (!$entity && !$entity->getPhoto()) {
                throw $this->createNotFoundException('Unable to find report entity.');
            }

            $entity->getTag()->setDeletedAt(new \DateTime());
            $em->merge($entity->getTag());
            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('report'));
    }

    /**
     * Deletes a re^rt entity.
     *
     * @Route("/{id}", name="report_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AdEntifyCoreBundle:Report')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find report entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('report'));
    }

    /**
     * Creates a form to delete a photo entity by report id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeletePhotoForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('report_photo_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete photo and report', 'attr' => array('class' => 'btn btn-danger btn-lg')))
            ->getForm()
            ;
    }

    /**
     * Creates a form to delete a tag entity by report id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteTagForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('report_tag_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete tag and report', 'attr' => array('class' => 'btn btn-danger btn-lg')))
            ->getForm()
            ;
    }

    /**
     * Creates a form to delete a Report entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('report_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete report without other actions', 'attr' => array('class' => 'btn btn-danger btn-lg')))
            ->getForm()
            ;
    }
}
