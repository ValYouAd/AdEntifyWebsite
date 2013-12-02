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

    /**
     * @param $avalancheService
     */
    public function __construct($filterManager, $imagine, $fileUploader) {
        $this->filterManager = $filterManager;
        $this->imagine = $imagine;
        $this->fileUploader = $fileUploader;
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

        foreach($thumb->getDesiredThumbSizes() as $size) {
            $path = FileTools::getUserPhotosPath($user, $size);

            // Generate thumb
            $generatedThumbs[$size] = $this->generateThumb($thumb, $size, $path, $filename);
        }

        return $generatedThumbs;
    }

    public function generateProductThumb(Thumb $thumb, $filename)
    {
        $generatedThumbs = array();
        foreach($thumb->getDesiredThumbSizes() as $size) {
            $path = FileTools::getProductPhotoPath($size);

            // Generate thumb
            $generatedThumbs[$size] = $this->generateThumb($thumb, $size, $path, $filename);
        }

        return $generatedThumbs;
    }

    public function generateProfilePictureThumb(Thumb $thumb, User $user, $filename)
    {
        $generatedThumbs = array();
        foreach($thumb->getDesiredThumbSizes() as $size) {
            $path = FileTools::getUserProfilePicturePath($user);

            // Generate thumb
            $generatedThumbs[$size] = $this->generateThumb($thumb, $size, $path, $filename);
        }

        return $generatedThumbs;
    }

    public function generateBrandLogoThumb(Thumb $thumb, $filename)
    {
        $generatedThumbs = array();
        foreach($thumb->getDesiredThumbSizes() as $size) {
            $path = FileTools::getBrandLogoPath($size);

            // Generate thumb
            $generatedThumbs[$size] = $this->generateThumb($thumb, $size, $path, $filename);
        }

        return $generatedThumbs;
    }

    /**
     * Generate Thumb
     *
     * @param $thumb
     * @param $size
     * @param $path
     * @param $filename
     * @return array
     */
    private function generateThumb($thumb, $size, $path, $filename)
    {
        $result = FileTools::loadFile($thumb->getOriginalPath());
        $imageContent = $this->resize($size, $result);
        $this->replace_extension($filename, 'jpg');
        $url = $this->fileUploader->uploadFromContent($imageContent, $result['content-type'], $path, $filename);

        $thumbInfo = array(
            'filename' => $url
        );
        $thumbInfo = $this->getImageSize($url, $thumbInfo);

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
        return $this->filterManager->get($size)
            ->apply($this->imagine->load($imageInfo['content']))
            ->get($this->getFormat($imageInfo['content-type']), array(
                'quality' => $this->filterManager->getOption($size, "quality", 100),
                'format'  => $this->filterManager->getOption($size, "format", null)
            ));;
    }

    /**
     * Get image size from image url
     *
     * @param $url
     * @param $array
     * @return array
     */
    private function getImageSize($url, $array) {
        $url = str_replace(' ', '%20', $url);
        $imageSize = getimagesize($url);

        return array_merge($array, array(
            'width' => $imageSize[0],
            'height' => $imageSize[1]
        ));
    }

    private function getFormat($mime)
    {
        static $formats = array(
            'image/jpeg' =>         'jpeg',
            'image/jpg' =>         'jpg',
            'image/gif' =>          'gif',
            'image/png' =>          'png',
            'image/vnd.wap.wbmp' => 'wbmp',
            'image/xbm' =>          'xbm',
        );

        return $formats[$mime];
    }

    private function getMimeType($format)
    {
        static $mimeTypes = array(
            'jpeg' => 'image/jpeg',
            'jpg'  => 'image/jpeg',
            'gif'  => 'image/gif',
            'png'  => 'image/png',
            'wbmp' => 'image/vnd.wap.wbmp',
            'xbm'  => 'image/xbm',
        );

        return $mimeTypes[$format];
    }

    function replace_extension($filename, $new_extension) {
        $info = pathinfo($filename);
        return $info['filename'] . '.' . $new_extension;
    }
}