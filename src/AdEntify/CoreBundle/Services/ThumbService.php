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
    protected $rootUrl;

    /**
     * @param $avalancheService
     */
    public function __construct($filterManager, $imagine, $rootUrl) {
        $this->filterManager = $filterManager;
        $this->imagine = $imagine;
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

        foreach($thumb->getDesiredThumbSizes() as $size) {
            $path = FileTools::getUserPhotosPath($user, $size);

            // Generate thumb
            $imageSize = $this->resize($thumb, $size, $path, $filename);

            $generatedThumbs[$size] = array(
                'filename' => $this->rootUrl . 'uploads/photos/users/' . $user->getId().'/' . $size . '/' . $filename,
                'width' => $imageSize[0],
                'height' => $imageSize[1],
            );
        }

        return $generatedThumbs;
    }

    public function generateProductThumb(Thumb $thumb, $filename)
    {
        $generatedThumbs = array();
        foreach($thumb->getDesiredThumbSizes() as $size) {
            $path = FileTools::getProductPhotoPath($size);

            // Generate thumb
            $imageSize = $this->resize($thumb, $size, $path, $filename);

            $generatedThumbs[$size] = array(
                'filename' => $filename,
                'width' => $imageSize[0],
                'height' => $imageSize[1],
            );
        }

        return $generatedThumbs;
    }

    private function resize($thumb, $size, $path, $filename)
    {
        $this->filterManager->get($size)
            ->apply($this->imagine->open($thumb->getOriginalPath()))
            ->save($path.$filename, array(
                'quality' => $this->filterManager->getOption($size, "quality", 100),
                'format'  => $this->filterManager->getOption($size, "format", null)
            ));

        // Get size of thumb
        return getimagesize($path.$filename);
    }
}