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
     */
    public function uploadExternalPhotos(Request $request)
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
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
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @Route("/upload/product-photo", methods="POST", name="upload_product_photo")
     */
    public function uploadProductPhoto()
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            if (isset($_FILES['files'])) {
                $uploadedFile = $_FILES['files'];
                $path = FileTools::getProductPhotoPath();
                $filename = uniqid().$uploadedFile['name'][0];
                $file = $this->getRequest()->files->get('files');
                $url = $this->getFileUploader()->upload($file[0], $path, $filename);
                if ($url) {
                    $thumb = new Thumb();
                    $thumb->setOriginalPath($url);
                    $thumb->addThumbSize(FileTools::PHOTO_SIZE_SMALLL);
                    $thumb->addThumbSize(FileTools::PHOTO_SIZE_MEDIUM);

                    $thumbs = $this->container->get('ad_entify_core.thumb')->generateProductThumb($thumb, $filename);
                    $thumbs['original'] = $url;
                    $response = new JsonResponse();
                    $response->setData($thumbs);
                    return $response;
                } else {
                    throw new HttpException(500);
                }
            } else {
                throw new NotFoundHttpException('No images to upload.');
            }
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @Route("/upload/profile-picture", methods="POST", name="upload_profile_picture")
     */
    public function uploadProfilePicture()
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            if (isset($_FILES['profilepicture'])) {
                $uploadedFile = $_FILES['profilepicture'];
                $user = $this->container->get('security.context')->getToken()->getUser();
                $path = FileTools::getUserProfilePicturePath($user);
                $filename = uniqid().$uploadedFile['name'][0];
                $url = $this->getFileUploader()->upload($this->getRequest()->files->get('profilepicture'), $path, $filename);
                if ($url) {
                    $thumb = new Thumb();
                    $thumb->setOriginalPath($url);
                    $thumb->addThumbSize(FileTools::PROFILE_PICTURE_TYPE);

                    $thumbs = $this->container->get('ad_entify_core.thumb')->generateProfilePictureThumb($thumb, $user, $filename);
                    $thumbs['original'] = $url;
                    $response = new JsonResponse();
                    $response->setData($thumbs);

                    $user->setProfilePicture($thumbs['profile-picture']['filename']);
                    $this->getDoctrine()->getManager()->merge($user);
                    $this->getDoctrine()->getManager()->flush();

                    return $response;
                } else {
                    throw new HttpException(500);
                }
            } else {
                throw new NotFoundHttpException('No profile picture to upload.');
            }
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @Route("/upload/local-photo", methods="POST", name="upload_local_photo")
     */
    public function uploadLocalPhoto()
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            if (isset($_FILES['files'])) {
                $uploadedFile = $_FILES['files'];
                $user = $this->container->get('security.context')->getToken()->getUser();
                $path = FileTools::getUserPhotosPath($user);
                $filename = uniqid().$uploadedFile['name'][0];
                $file = $this->getRequest()->files->get('files');

                $url = $this->getFileUploader()->upload($file[0], $path, $filename);
                if ($url) {
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
                    $thumb->setOriginalPath($url);
                    $thumb->configureThumbs();
                    $thumbs = $this->container->get('ad_entify_core.thumb')->generateUserPhotoThumb($thumb, $user, $filename);

                    // Add original
                    $originalImageSize = getimagesize($url);
                    $thumbs['original'] = array(
                        'filename' => $url,
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
        } else {
            throw new HttpException(401);
        }
    }

    /**
     * @Route("/upload/logo-photo", methods="POST", name="upload_logo_photo")
     */
    public function uploadLogoPhoto()
    {
        $securityContext = $this->container->get('security.context');
        if ($securityContext->isGranted('IS_AUTHENTICATED_FULLY')) {
            if (isset($_FILES['original_logo_url'])) {
                $uploadedFile = $_FILES['original_logo_url'];
                $path = FileTools::getBrandLogoPath();
                $filename = uniqid().$uploadedFile['name'][0];
                $file = $this->getRequest()->files->get('original_logo_url');
                $url = $this->getFileUploader()->upload($file, $path, $filename);
                if ($url) {
                    $thumb = new Thumb();
                    $thumb->setOriginalPath($url);
                    $thumb->addThumbSize(FileTools::PHOTO_SIZE_SMALLL);
                    $thumb->addThumbSize(FileTools::PHOTO_SIZE_MEDIUM);

                    $thumbs = $this->container->get('ad_entify_core.thumb')->generateBrandLogoThumb($thumb, $filename);
                    $thumbs['original'] = $url;
                    $response = new JsonResponse();
                    $response->setData($thumbs);
                    return $response;
                } else {
                    throw new HttpException(500);
                }
            } else {
                throw new NotFoundHttpException('No images to upload.');
            }
        } else {
            throw new HttpException(401);
        }
    }

    protected function getFileUploader() {
        return $this->get('adentify_storage.file_manager');
    }
}