<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 24/05/2013
 * Time: 09:50
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Model;


class Thumb
{
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
}