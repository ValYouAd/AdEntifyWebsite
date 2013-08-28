<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 18/04/2013
 * Time: 18:47
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Form\CommentType;
use Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\Controller\Annotations\Prefix,
    FOS\RestBundle\Controller\Annotations\NamePrefix,
    FOS\RestBundle\Controller\Annotations\RouteResource,
    FOS\RestBundle\Controller\Annotations\View,
    FOS\RestBundle\Controller\Annotations\QueryParam,
    FOS\RestBundle\Controller\FOSRestController;

use Doctrine\Common\Collections\ArrayCollection,
    Doctrine\Common\Collections\Collection;

use AdEntify\CoreBundle\Entity\Comment;

/**
 * Class CommentsController
 * @package AdEntify\CoreBundle\Controller
 *
 * @Prefix("api/v1")
 * @NamePrefix("api_v1_")
 *
 * @RouteResource("Comment")
 */
class CommentsController extends FosRestController
{
    /**
     * @View()
     */
    public function cgetAction()
    {
        return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Comment')->findAll();
    }

    /**
     * @View()
     *
     * @return Comment
     */
    public function getAction($id)
    {
        return $this->getDoctrine()->getManager()->getRepository('AdEntifyCoreBundle:Comment')->find($id);
    }

    /**
     * @View()
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $comment = new Comment();
        $form = $this->getForm($comment);
        $form->bind($request);
        if ($form->isValid()) {
            $comment->setAuthor($user);
            $em->persist($comment);
            $em->flush();

            return $comment;
        } else {
            return $form;
        }
    }

    /**
     * Get form for Comment
     *
     * @param null $comment
     * @return mixed
     */
    protected function getForm($comment = null)
    {
        return $this->createForm(new CommentType(), $comment);
    }
}