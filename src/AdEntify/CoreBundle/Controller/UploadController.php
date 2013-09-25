<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 16/05/2013
 * Time: 14:28
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Controller;

use AdEntify\CoreBundle\Entity\LocalUpload;
use AdEntify\CoreBundle\Entity\Photo;
use AdEntify\CoreBundle\Entity\Task;
use AdEntify\CoreBundle\Entity\User;
use AdEntify\CoreBundle\Model\Thumb;
use AdEntify\CoreBundle\Util\FileTools;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UploadController extends Controller
{
    /**
     * @Route("/upload/load-external-photos", methods="POST", name="upload_load_external_photos")
     * @Secure("ROLE_USER, ROLE_FACEBOOK, ROLE_TWITTER")
     */
    public function uploadExternalPhotos(Request $request)
    {
        $response = null;
        $post = $request->request;
        if ($post->has('images')) {
            $user = $this->container->get('security.context')->getToken()->getUser();
            $em = $this->getDoctrine()->getManager();
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
            return $response;
        } else {
            throw new NotFoundHttpException('No images to upload.');
        }
    }

    /**
     * @Route("/upload/product-photo", methods="POST", name="upload_product_photo")
     * @Secure("ROLE_USER, ROLE_FACEBOOK, ROLE_TWITTER")
     */
    public function uploadProductPhoto()
    {
        if (isset($_FILES['files'])) {
            $uploadedFile = $_FILES['files'];
            $path = FileTools::getProductPhotoPath();
            $filename = uniqid().$uploadedFile['name'][0];
            $file = move_uploaded_file($uploadedFile['tmp_name'][0], $path.$filename);
            if ($file) {
                $thumb = new Thumb();
                $thumb->setOriginalPath($path.$filename);
                $thumb->addThumbSize(FileTools::PHOTO_TYPE_SMALLL);
                $thumb->addThumbSize(FileTools::PHOTO_TYPE_MEDIUM);

                $thumbs = $this->container->get('ad_entify_core.thumb')->generateProductThumb($thumb, $filename);
                $thumbs['original'] = $filename;
                $response = new JsonResponse();
                $response->setData($thumbs);
                return $response;
            } else {
                throw new HttpException(500);
            }
        } else {
            throw new NotFoundHttpException('No images to upload.');
        }
    }

    /**
     * @Route("/upload/local-photo", methods="POST", name="upload_local_photo")
     * @Secure("ROLE_USER, ROLE_FACEBOOK, ROLE_TWITTER")
     */
    public function uploadLocalPhoto()
    {
        if (isset($_FILES['files'])) {
            $uploadedFile = $_FILES['files'];
            $user = $this->container->get('security.context')->getToken()->getUser();
            $path = FileTools::getUserPhotosPath($user);
            $filename = uniqid().$uploadedFile['name'][0];
            $file = move_uploaded_file($uploadedFile['tmp_name'][0], $path.$filename);
            if ($file) {
                $em = $this->getDoctrine()->getManager();
                $userLocalUpload = $em->getRepository('AdEntifyCoreBundle:LocalUpload')->findOneBy(array(
                    'owner' => $user->getId()
                ));

                if (!$userLocalUpload) {
                    $userLocalUpload = new LocalUpload();
                    $userLocalUpload->setOwner($user)->setUploadedPhotos(json_encode(array(
                        $filename
                    )));
                    $em->persist($userLocalUpload);
                } else {
                    $uploadedPhotos = json_decode($userLocalUpload->getUploadedPhotos());
                    $uploadedPhotos[] = $filename;
                    $userLocalUpload->setUploadedPhotos(json_encode($uploadedPhotos));
                    $em->merge($userLocalUpload);
                }
                $em->flush();

                $thumb = new Thumb();
                $thumb->setOriginalPath($path.$filename);
                $thumb->addThumbSize(FileTools::PHOTO_TYPE_LARGE);
                $thumb->addThumbSize(FileTools::PHOTO_TYPE_MEDIUM);
                $thumb->addThumbSize(FileTools::PHOTO_TYPE_SMALLL);
                $thumbs = $this->container->get('ad_entify_core.thumb')->generateUserPhotoThumb($thumb, $user, $filename);

                // Add original
                $originalImageSize = getimagesize($path.$filename);
                $thumbs['original'] = array(
                    'filename' => $this->container->getParameter('root_url') . 'uploads/photos/users/' . $user->getId().'/original/' . $filename,
                    'width' => $originalImageSize[0],
                    'height' => $originalImageSize[1],
                );

                $response = new JsonResponse();
                $response->setData($thumbs);
                return $response;
            } else {
                throw new HttpException(500);
            }
        } else {
            throw new NotFoundHttpException('No images to upload.');
        }
    }
}