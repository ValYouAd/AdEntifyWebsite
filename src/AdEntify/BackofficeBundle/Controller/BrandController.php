<?php

namespace AdEntify\BackofficeBundle\Controller;

use AdEntify\CoreBundle\Model\Thumb;
use AdEntify\CoreBundle\Util\FileTools;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use AdEntify\CoreBundle\Entity\Brand;
use AdEntify\BackofficeBundle\Form\BrandType;

/**
 * Brand controller.
 *
 * @Route("/brands")
 */
class BrandController extends Controller
{
    const PAGE_LIMIT = 10;

    /**
     * Lists all Brand entities.
     *
     * @Route("/{page}", requirements={"page" = "\d+"}, defaults={"page" = 1}, name="brands")
     * @Method("GET")
     * @Template()
     */
    public function indexAction($page = 1)
    {
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQuery('SELECT brand FROM AdEntify\CoreBundle\Entity\Brand brand')
            ->setFirstResult(($page - 1) * self::PAGE_LIMIT)
            ->setMaxResults(self::PAGE_LIMIT);

        $paginator = new Paginator($query, true);

        $c = count($paginator);

        return array(
            'entities' => $paginator,
            'count' => $c,
            'pageLimit' => self::PAGE_LIMIT
        );
    }

    /**
     * Creates a new Brand entity.
     *
     * @Route("/", name="brands_create")
     * @Method("POST")
     * @Template("AdEntifyBackofficeBundle:Brand:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Brand();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);

            if ($entity->getOriginalLogoUrl()) {
                $uploadableManager = $this->container->get('stof_doctrine_extensions.uploadable.manager');
                $uploadableListener = $uploadableManager->getUploadableListener();
                $uploadableListener->setDefaultPath(FileTools::getBrandLogoPath(FileTools::LOGO_TYPE_ORIGINAL, false));
                $uploadableManager->markEntityToUpload($entity, $entity->getOriginalLogoUrl());
            } elseif ($entity->getLogoUrl()) {
                $logo = FileTools::downloadImage($entity->getLogoUrl(), FileTools::getBrandLogoPath());
                if ($logo['status'] !== false) {
                    $entity->setOriginalLogoUrl($logo['path'].$logo['filename']);
                } else {
                    return array(
                        'entity' => $entity,
                        'form'   => $form->createView(),
                    );
                }
            }

            $em->flush();

            $thumb = new Thumb();
            $filename = basename($entity->getOriginalLogoUrl());
            $thumb->setOriginalPath(FileTools::getBrandLogoPath().$filename);
            $thumb->addThumbSize(FileTools::LOGO_TYPE_LARGE);
            $thumb->addThumbSize(FileTools::LOGO_TYPE_MEDIUM);
            $thumb->addThumbSize(FileTools::LOGO_TYPE_SMALLL);
            $thumbs = $this->container->get('ad_entify_core.thumb')->generateBrandLogoThumb($thumb, $filename);

            $entity->setSmallLogoUrl(FileTools::getBrandLogoPath(FileTools::LOGO_TYPE_SMALLL, false).$thumbs[FileTools::LOGO_TYPE_SMALLL]['filename']);
            $entity->setMediumLogoUrl(FileTools::getBrandLogoPath(FileTools::LOGO_TYPE_MEDIUM, false).$thumbs[FileTools::LOGO_TYPE_MEDIUM]['filename']);
            $entity->setLargeLogoUrl(FileTools::getBrandLogoPath(FileTools::LOGO_TYPE_LARGE, false).$thumbs[FileTools::LOGO_TYPE_LARGE]['filename']);

            $em->merge($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('brands_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
    * Creates a form to create a Brand entity.
    *
    * @param Brand $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createCreateForm(Brand $entity)
    {
        $form = $this->createForm(new BrandType(), $entity, array(
            'action' => $this->generateUrl('brands_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create', 'attr' => array('class' => 'btn btn-success')));

        return $form;
    }

    /**
     * Displays a form to create a new Brand entity.
     *
     * @Route("/new", name="brands_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Brand();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Brand entity.
     *
     * @Route("/show/{id}", name="brands_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AdEntifyCoreBundle:Brand')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Brand entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Brand entity.
     *
     * @Route("/{id}/edit", name="brands_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AdEntifyCoreBundle:Brand')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Brand entity.');
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
    * Creates a form to edit a Brand entity.
    *
    * @param Brand $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Brand $entity)
    {
        $form = $this->createForm(new BrandType(), $entity, array(
            'action' => $this->generateUrl('brands_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update', 'attr' => array('class' => 'btn btn-success')));

        return $form;
    }
    /**
     * Edits an existing Brand entity.
     *
     * @Route("/{id}", name="brands_update")
     * @Method("PUT")
     * @Template("AdEntifyBackofficeBundle:Brand:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('AdEntifyCoreBundle:Brand')->find($id);
        $logoUrl = $entity->getOriginalLogoUrl();

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Brand entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $file = $request->files->get('adentify_backofficebundle_brand');
            if (($file && isset($file['originalLogoUrl'])) || $entity->getLogoUrl()) {
                if ($file && isset($file['originalLogoUrl'])) {
                    $uploadableManager = $this->container->get('stof_doctrine_extensions.uploadable.manager');
                    $uploadableListener = $uploadableManager->getUploadableListener();
                    $uploadableListener->setDefaultPath(FileTools::getBrandLogoPath(FileTools::LOGO_TYPE_ORIGINAL, false));
                    $uploadableManager->markEntityToUpload($entity, $entity->getOriginalLogoUrl());
                } elseif ($entity->getLogoUrl()) {
                    $logo = FileTools::downloadImage($entity->getLogoUrl(), FileTools::getBrandLogoPath());
                    if ($logo['status'] !== false) {
                        $entity->setOriginalLogoUrl($logo['path'].$logo['filename']);
                    } else {
                        return $this->redirect($this->generateUrl('brands_edit', array('id' => $id)));
                    }
                }

                $em->flush();

                $thumb = new Thumb();
                $filename = basename($entity->getOriginalLogoUrl());
                $thumb->setOriginalPath(FileTools::getBrandLogoPath().$filename);
                $thumb->addThumbSize(FileTools::LOGO_TYPE_LARGE);
                $thumb->addThumbSize(FileTools::LOGO_TYPE_MEDIUM);
                $thumb->addThumbSize(FileTools::LOGO_TYPE_SMALLL);
                $thumbs = $this->container->get('ad_entify_core.thumb')->generateBrandLogoThumb($thumb, $filename);

                $entity->setSmallLogoUrl(FileTools::getBrandLogoPath(FileTools::LOGO_TYPE_SMALLL, false).$thumbs[FileTools::LOGO_TYPE_SMALLL]['filename']);
                $entity->setMediumLogoUrl(FileTools::getBrandLogoPath(FileTools::LOGO_TYPE_MEDIUM, false).$thumbs[FileTools::LOGO_TYPE_MEDIUM]['filename']);
                $entity->setLargeLogoUrl(FileTools::getBrandLogoPath(FileTools::LOGO_TYPE_LARGE, false).$thumbs[FileTools::LOGO_TYPE_LARGE]['filename']);

                $em->merge($entity);
            } else {
                $entity->setOriginalLogoUrl($logoUrl);
            }

            $em->flush();

            return $this->redirect($this->generateUrl('brands_show', array('id' => $entity->getId())));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Brand entity.
     *
     * @Route("/{id}", name="brands_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('AdEntifyCoreBundle:Brand')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Brand entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('brands'));
    }

    /**
     * Creates a form to delete a Brand entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('brands_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete', 'attr' => array('class' => 'btn btn-danger')))
            ->getForm()
        ;
    }
}
