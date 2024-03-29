<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 24/05/2013
 * Time: 09:26
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Services;

use AdEntify\CoreBundle\Entity\User;
use AdEntify\CoreBundle\Model\Thumb;
use AdEntify\CoreBundle\Util\FileTools;

class ThumbService
{
    protected $filterManager;
    protected $imagine;
    protected $fileUploader;
    protected $rootUrl;

    /**
     * @param $avalancheService
     */
    public function __construct($filterManager, $imagine, $fileUploader, $rootUrl) {
        $this->filterManager = $filterManager;
        $this->imagine = $imagine;
        $this->fileUploader = $fileUploader;
        $this->rootUrl = $rootUrl;
    }

    /**
     * @param Thumb $thumb
     * @param User $user
     * @return array thumb generated
     */
    public function generateUserPhotoThumb(Thumb $thumb, User $user, $filename = '')
    {
        $generatedThumbs = array();
        // Generate unique filename if empty
        if (empty($filename))
            $filename = uniqid().FileTools::getExtensionFromUrl($thumb->getOriginalPath());

        $fileInfo = FileTools::loadRemoteFile($thumb->getOriginalPath());
        foreach ($thumb->getDesiredThumbSizes() as $size) {
            $path = FileTools::getUserPhotosPath($user, $size);

            // Generate thumb
            $generatedThumbs[$size] = $this->generateThumb($size, $path, $filename, $fileInfo);
        }

        $this->consolidateThumbs($generatedThumbs, $thumb);

        return $generatedThumbs;
    }

    /**
     * Generate product thumb
     *
     * @param Thumb $thumb
     * @param $filename
     * @return array
     */
    public function generateProductThumb(Thumb $thumb, $filename)
    {
        $generatedThumbs = array();
        $fileInfo = FileTools::loadRemoteFile($thumb->getOriginalPath(), 10, true);
        foreach($thumb->getDesiredThumbSizes() as $size) {
            $path = FileTools::getProductPhotoPath($size);

            // Generate thumb
            $generatedThumbs[$size] = $this->generateThumb($size, $path, $filename, $fileInfo);
        }

        $this->consolidateThumbs($generatedThumbs, $thumb);

        return $generatedThumbs;
    }

    /***
     * Generate profile picture thumbs
     *
     * @param Thumb $thumb
     * @param User $user
     * @param $filename
     * @return array
     */
    public function generateProfilePictureThumb(Thumb $thumb, User $user, $filename)
    {
        $generatedThumbs = array();
        $fileInfo = FileTools::loadRemoteFile($thumb->getOriginalPath(), 10, false);
        foreach($thumb->getDesiredThumbSizes() as $size) {
            $path = FileTools::getUserProfilePicturePath($user);

            // Generate thumb
            $generatedThumbs[$size] = $this->generateThumb($size, $path, $filename, $fileInfo);
        }

        $this->consolidateThumbs($generatedThumbs, $thumb);

        return $generatedThumbs;
    }

    /**
     * Generate brand logo thumbs
     *
     * @param Thumb $thumb
     * @param $filename
     * @return array
     */
    public function generateBrandLogoThumb(Thumb $thumb, $filename)
    {
        $generatedThumbs = array();
        $fileInfo = FileTools::loadRemoteFile($thumb->getOriginalPath(), 10, true);
        foreach($thumb->getDesiredThumbSizes() as $size) {
            $path = FileTools::getBrandLogoPath($size);
            // Generate thumb
            $generatedThumbs[$size] = $this->generateThumb($size, $path, $filename, $fileInfo);
        }

        $this->consolidateThumbs($generatedThumbs, $thumb);

        return $generatedThumbs;
    }

    /**
     * Generate thumb
     *
     * @param $size
     * @param $path
     * @param $filename
     * @param $fileinfo
     * @return array
     */
    private function generateThumb($size, $path, $filename, $fileInfo)
    {
        $imageContent = $this->resize($size, $fileInfo);
        if (!$imageContent)
            return null;

        $filename = $this->replace_extension($filename, 'jpg');
        $url = $this->fileUploader->uploadFromContent($imageContent, 'image/jpeg', $path, $filename);

        $thumbInfo = array(
            'filename' => $url
        );
        $thumbInfo = $this->getImageSizeFormUrl($url, $thumbInfo);

        return $thumbInfo;
    }

    /**
     * Resize image
     *
     * @param $thumb
     * @param $size
     * @return mixed
     */
    private function resize($size, $imageInfo)
    {
        $sizes = $this->filterManager->getOption($size, "size", null);
        if (array_key_exists('width', $imageInfo) && $sizes && is_array($sizes) && $sizes[0] > $imageInfo['width']) {
            return false;
        } else {
            return $this->filterManager->get($size)
                ->apply($this->imagine->load($imageInfo['content']))
                ->get('jpeg', array(
                    'quality' => $this->filterManager->getOption($size, "quality", 100),
                    'format'  => $this->filterManager->getOption($size, "format", 'jpeg')
                ));
        }
    }

    /**
     * Get image size from image url
     *
     * @param $url
     * @param $array
     * @return array
     */
    private function getImageSizeFormUrl($url, $array) {
        $url = str_replace(' ', '%20', $url);
        $imageSize = getimagesize($url);

        return array_merge($array, array(
            'width' => $imageSize[0],
            'height' => $imageSize[1]
        ));
    }

    private function consolidateThumbs(&$generatedThumbs, Thumb $thumb)
    {
        foreach ($generatedThumbs as $key => $generatedThumb) {
            if (!$generatedThumb) {
                $generatedThumbs[$key] = array(
                    'filename' => $thumb->getOriginalPath()
                );
            }
        }
    }

    function replace_extension($filename, $new_extension) {
        $info = pathinfo($filename);
        return $info['filename'] . '.' . $new_extension;
    }
}