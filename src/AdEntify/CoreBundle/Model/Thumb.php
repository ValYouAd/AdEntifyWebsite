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

    private function generateThumbIfOriginalLarger($size, $photoType, Photo $photo)
    {
        $width = $photo->getOriginalWidth();
        if (!$width)
            $width = getimagesize($photo->getOriginalUrl())[0];

        if ($width < $size) {
            if ($size == self::MEDIUM_SIZE) {
                $photo->setMediumUrl($photo->getOriginalUrl());
                $photo->setMediumWidth($photo->getOriginalWidth());
                $photo->setMediumHeight($photo->getOriginalHeight());
            } else if ($size == self::LARGE_SIZE) {
                $photo->setLargeUrl($photo->getOriginalUrl());
                $photo->setLargeWidth($photo->getOriginalWidth());
                $photo->setLargeHeight($photo->getOriginalHeight());
            } else if ($size == self::RETINA_SIZE) {
                $photo->setRetinaUrl($photo->getOriginalUrl());
                $photo->setRetinaWidth($photo->getOriginalWidth());
                $photo->setRetinaHeight($photo->getOriginalHeight());
            }
        } else {
            // Original larger, add it to generate the thumb
            $this->addThumbSize($photoType);
        }
    }
}