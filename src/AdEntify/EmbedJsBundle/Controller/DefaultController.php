<?php

namespace AdEntify\EmbedJsBundle\Controller;

use AdEntify\CoreBundle\Entity\Photo;
use AdEntify\CoreBundle\Entity\Tag;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DefaultController extends Controller
{
    /**
     * @Route("/embed/{id}.js", requirements={"id"= "\d+"}, name="js_embed")
     * @Template()
     */
    public function embedAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $photo = $em->createQuery('SELECT photo FROM AdEntify\CoreBundle\Entity\Photo photo
                LEFT JOIN photo.tags tag WITH (tag.visible = true AND tag.deletedAt IS NULL AND tag.censored = false AND tag.validationStatus != :denied)
                LEFT JOIN photo.owner owner
                WHERE photo.id = :id AND photo.status = :status AND photo.visibilityScope = :visibilityScope')
            ->setParameters(array(
                ':status' => Photo::STATUS_READY,
                ':visibilityScope' => Photo::SCOPE_PUBLIC,
                ':id' => $id,
                ':denied' => Tag::VALIDATION_DENIED,
            ))
            ->getOneOrNullResult();

        if ($photo) {
            $response = new Response();
            $response->setContent($this->renderView('AdEntifyEmbedJsBundle:Default:embed.js.twig', array(
                'photo' => $photo,
                'rootUrl' => $this->generateUrl('root_url', array(), true)
            )));
            $response->setStatusCode(200);
            $response->headers->set('Content-Type', 'text/javascript; charset=utf-8');
            return $response;
        } else
            throw new NotFoundHttpException('Photo not found');
    }

    /**
     * @Route("/iframe/photo-{id}.html", requirements={"id"= "\d+"}, name="iframe_photo")
     * @Template()
     *
     * @param $id
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function iframeAction($id)
    {
        $showTags = $this->getRequest()->query->has('show-tags') ? true : false;
        $showLikes = $this->getRequest()->query->has('show-likes') ? true : false;
        $cover = $this->getRequest()->query->has('cover') ? true : false;

        $em = $this->getDoctrine()->getManager();
        $photo = $em->createQuery('SELECT photo FROM AdEntify\CoreBundle\Entity\Photo photo LEFT JOIN photo.owner owner
                LEFT JOIN photo.tags tag WITH (tag.visible = true AND tag.deletedAt IS NULL AND tag.censored = false AND tag.validationStatus != :denied)
                WHERE photo.id = :id AND photo.status = :status AND photo.visibilityScope = :visibilityScope')
            ->setParameters(array(
                ':status' => Photo::STATUS_READY,
                ':visibilityScope' => Photo::SCOPE_PUBLIC,
                ':id' => $id,
                ':denied' => Tag::VALIDATION_DENIED,
            ))
            ->getOneOrNullResult();

        if ($photo) {
            return array(
                'photo' => $photo,
                'showTags' => $showTags,
                'showLikes' => $showLikes,
                'cover' => $cover
            );
        } else
            throw new NotFoundHttpException('Photo not found');
    }
}
