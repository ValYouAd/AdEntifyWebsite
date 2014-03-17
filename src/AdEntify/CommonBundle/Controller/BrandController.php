<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 09/01/2014
 * Time: 19:21
 */

namespace AdEntify\CommonBundle\Controller;

use AdEntify\CommonBundle\Form\BrandType;
use AdEntify\CoreBundle\Entity\Brand;
use AdEntify\CoreBundle\Model\Thumb;
use AdEntify\CoreBundle\Util\FileTools;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Brand controller.
 *
 * @Route("/brands")
 */
class BrandController extends Controller
{
    /**
     * Creates a new Brand entity.
     *
     * @Route("/", name="public_brands_create")
     * @Method("POST")
     * @Template("AdEntifyCommonBundle:Brand:new.html.twig")
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
            }

            $em->flush();

            if ($entity->getOriginalLogoUrl()) {
                $thumb = new Thumb();
                $filename = basename($entity->getOriginalLogoUrl());
                $thumb->setOriginalPath($entity->getOriginalLogoUrl());
                $thumb->addThumbSize(FileTools::LOGO_TYPE_LARGE);
                $thumb->addThumbSize(FileTools::LOGO_TYPE_MEDIUM);
                $thumb->addThumbSize(FileTools::LOGO_TYPE_SMALLL);
                $thumbs = $this->container->get('ad_entify_core.thumb')->generateBrandLogoThumb($thumb, $filename);

                $entity->setSmallLogoUrl(FileTools::getBrandLogoPath(FileTools::LOGO_TYPE_SMALLL, false).$thumbs[FileTools::LOGO_TYPE_SMALLL]['filename']);
                $entity->setMediumLogoUrl(FileTools::getBrandLogoPath(FileTools::LOGO_TYPE_MEDIUM, false).$thumbs[FileTools::LOGO_TYPE_MEDIUM]['filename']);
                $entity->setLargeLogoUrl(FileTools::getBrandLogoPath(FileTools::LOGO_TYPE_LARGE, false).$thumbs[FileTools::LOGO_TYPE_LARGE]['filename']);
            }

            $em->merge($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('public_brands_create_success'));
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
            'action' => $this->generateUrl('public_brands_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create', 'attr' => array('class' => 'btn btn-success')));

        return $form;
    }

    /**
     * Displays a form to create a new Brand entity.
     *
     * @Route("/new", name="public_brands_new")
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
     * @Route("/success", name="public_brands_create_success")
     * @Template()
     */
    public function createSuccessAction()
    {
        return array();
    }
} 