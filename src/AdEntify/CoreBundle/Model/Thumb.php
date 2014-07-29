<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 24/05/2013
 * Time: 09:50
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Model;


use AdEntify\CoreBundle\Entity\Photo;
use AdEntify\CoreBundle\Util\FileTools;

class Thumb
{
    const SMALL_SIZE = 100;
    const MEDIUM_SIZE = 320;
    const RETINA_SIZE = 640;
    const LARGE_SIZE = 1024;

    private $originalPath;
    private $desiredThumbSizes = array();

    /**
     * @param mixed $originalPath
     */
    public function setOriginalPath($originalPath)
    {
        $this->originalPath = $originalPath;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOriginalPath()
    {
        return $this->originalPath;
    }

    /**
     * @param array $desiredThumbSizes
     */
    public function setDesiredThumbSizes($desiredThumbSizes)
    {
        $this->desiredThumbSizes = $desiredThumbSizes;
        return $this;
    }

    /**
     * @return array
     */
    public function getDesiredThumbSizes()
    {
        return $this->desiredThumbSizes;
    }

    /**
     * Add thumb size
     *
     * @param $size
     */
    public function addThumbSize($size) {
        $this->desiredThumbSizes[] = $size;
    }

    /**
     * Check if thumb generation is needed
     *
     * @return bool
     */
    public function IsThumbGenerationNeeded() {
        return count($this->desiredThumbSizes) > 0 ? true : false;
    }

    /**
     * Check which thumbs needed to be generated
     *
     * @param Photo $photo
     */
    public function configure(Photo $photo)
    {
        $this->addThumbSize(FileTools::PHOTO_SIZE_SMALLL);

        $this->generateThumbIfOriginalLarger(Thumb::RETINA_SIZE, FileTools::PHOTO_SIZE_RETINA, $photo);
        $this->generateThumbIfOriginalLarger(Thumb::MEDIUM_SIZE, FileTools::PHOTO_SIZE_MEDIUM, $photo);
        $this->generateThumbIfOriginalLarger(Thumb::LARGE_SIZE, FileTools::PHOTO_SIZE_LARGE, $photo);
    }

    /**
     * Check which thumbs needed to be generated
     */
    public function configureThumbs()
    {
        $this->addThumbSize(FileTools::PHOTO_SIZE_SMALLL);

        $this->generateThumbIfOriginalLarger(Thumb::RETINA_SIZE, FileTools::PHOTO_SIZE_RETINA);
        $this->generateThumbIfOriginalLarger(Thumb::MEDIUM_SIZE, FileTools::PHOTO_SIZE_MEDIUM);
        $this->generateThumbIfOriginalLarger(Thumb::LARGE_SIZE, FileTools::PHOTO_SIZE_LARGE);
    }

    private function generateThumbIfOriginalLarger($size, $photoType, Photo $photo = null)
    {
        $width = 0;
        $height = 0;
        if ($photo) {
            $width = $photo->getOriginalWidth();
            $height = $photo->getOriginalHeight();
            if (!$width)
            {
                $imagesize = getimagesize($photo->getOriginalUrl() ? $photo->getOriginalUrl() : $this->getOriginalPath());
                $width = $imagesize[0];
                $height = $imagesize[1];
            }
        } else {
            $imagesize = getimagesize($this->getOriginalPath());
            $width = $imagesize[0];
            $height = $imagesize[1];
        }

        if ($width < $size) {
            $url = $photo && $photo->getOriginalUrl() ? $photo->getOriginalUrl() : $this->getOriginalPath();
            if ($size == self::MEDIUM_SIZE) {
                if ($photo) {
                    $photo->setMediumUrl($url);
                    $photo->setMediumWidth($width);
                    $photo->setMediumHeight($height);
                }
            } else if ($size == self::LARGE_SIZE) {
                if ($photo) {
                    $photo->setLargeUrl($url);
                    $photo->setLargeWidth($width);
                    $photo->setLargeHeight($height);
                }
            } else if ($size == self::RETINA_SIZE) {
                if ($photo) {
                    $photo->setRetinaUrl($url);
                    $photo->setRetinaWidth($width);
                    $photo->setRetinaHeight($height);
                }
            }
        } else {
            // Original larger, add it to generate the thumb
            $this->addThumbSize($photoType);
        }
    }
}