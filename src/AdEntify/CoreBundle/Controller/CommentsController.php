<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 18/04/2013
 * Time: 18:47
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\Action;
use AdEntify\CoreBundle\Entity\Notification;
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
use Symfony\Component\HttpKernel\Exception\HttpException;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

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
        return $this->getDoctrine()->getManager()->createQuery('SELECT comment FROM AdEntifyCoreBundle:Comment comment
            WHERE comment.deletedAt IS NULL')->getResult();
    }

    /**
     * @View()
     *
     * @return Comment
     */
    public function getAction($id)
    {
        return $this->getDoctrine()->getManager()->createQuery('SELECT comment FROM AdEntifyCoreBundle:Comment comment
            WHERE comment.deletedAt IS NULL and comment.id = :id')->setParameter('id', $id)->getOneOrNullResult();
    }

    /**
     * @View()
     */
    public function postAction(Request $request)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {

        } else {
            throw new HttpException(401);
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->container->get('security.context')->getToken()->getUser();

        $comment = new Comment();
        $form = $this->getForm($comment);
        $form->bind($request);
        if ($form->isValid()) {
            $comment->setAuthor($user);

            // COMMENT Action & notification
            $sendNotification = $user->getId() != $comment->getPhoto()->getOwner()->getId();
            $em->getRepository('AdEntifyCoreBundle:Action')->createAction(Action::TYPE_PHOTO_COMMENT,
                $user, $comment->getPhoto()->getOwner(), array($comment->getPhoto()),
                Action::getVisibilityWithPhotoVisibility($comment->getPhoto()->getVisibilityScope()), $comment->getPhoto()->getId(),
                $em->getClassMetadata(get_class($comment->getPhoto()))->getName(), $sendNotification, 'memberCommentPhoto');

            $em->persist($comment);
            $em->flush();

            return $comment;
        } else {
            return $form;
        }
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Delete a comment",
     *  statusCodes={
     *      204="Returned if the comment is deleted",
     *      403="Returned if the user is not authorized to delete the comment"
     *  },
     *  section="Tag"
     * )
     *
     * @View()
     *
     * @param $id
     */
    public function deleteAction($id)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            $comment = $this->getAction($id);
            if (!$comment)
                throw new HttpException(404);

            $user = $this->container->get('security.context')->getToken()->getUser();
            if ($user->getId() == $comment->getAuthor()->getId() && !$comment->getDeletedAt()) {
                $em = $this->getDoctrine()->getManager();
                $comment->setDeletedAt(new \DateTime());
                $em->merge($comment);

                $comment->getPhoto()->setCommentsCount($comment->getPhoto()->getCommentsCount() - 1);
                $em->merge($comment->getPhoto());

                $em->flush();
            } else {
                throw new HttpException(403, 'You are not authorized to delete this comment');
            }
        } else {
            throw new HttpException(401);
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
