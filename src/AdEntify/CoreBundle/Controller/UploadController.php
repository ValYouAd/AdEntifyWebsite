<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 16/05/2013
 * Time: 14:28
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\Photo;
use AdEntify\CoreBundle\Entity\Task;
use AdEntify\CoreBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Response;

class UploadController extends Controller
{
    /**
     * @Route("/upload/load-external-photos", methods="POST", name="upload_load_external_photos")
     * @Secure("ROLE_USER, ROLE_FACEBOOK, ROLE_TWITTER")
     */
    public function uploadPhotos()
    {
        $response = null;
        $user = $this->container->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $post = $this->getRequest()->request;
        if ($post->has('images')) {

            $images = array(
                'images' => $post->get('images')
            );
            if ($post->has('source'))
                $images['source'] = $post->get('source');
            $images = json_encode($images);

            // Create task
            $task = new Task();
            $task->setUser($user)->setMessage($images)->setNotifyCompleted(true)->setType(Task::TYPE_UPLOAD);
            $em->persist($task);
            $em->flush();

            $response = new Response('Upload started.');
        } else {
            throw new NotFoundHttpException('No images to upload.');
        }

        return $response;
    }
}