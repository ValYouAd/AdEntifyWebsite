<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 16/05/2013
 * Time: 15:25
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Util;

class FileTools
{
    const PHOTO_TYPE_ORIGINAL = 'original';
    const PHOTO_TYPE_LARGE = 'large';
    const PHOTO_TYPE_MEDIUM = 'medium';
    const PHOTO_TYPE_SMALLL = 'small';

    const LOGO_TYPE_ORIGINAL = 'logo-original';
    const LOGO_TYPE_LARGE = 'logo-large';
    const LOGO_TYPE_MEDIUM = 'logo-medium';
    const LOGO_TYPE_SMALLL = 'logo-small';

    /**
     * @param $url
     * @return string
     */
    public static function getExtensionFromUrl($url)
    {
        $pathParts = pathinfo($url);
        return '.'.$pathParts['extension'];
    }

    /**
     * @param $user
     * @param string $type
     *
     * @return string path
     */
    public static function getUserPhotosPath($user, $type = self::PHOTO_TYPE_ORIGINAL)
    {
        $path = __DIR__.'/../../../../web/uploads/photos/users/'.$user->getId().'/'.$type.'/';
        FileTools::createDirIfNotExist($path);
        return $path;
    }

    /**
     * @return string path
     */
    public static function getProductPhotoPath($type = self::PHOTO_TYPE_ORIGINAL)
    {
        $path = __DIR__.'/../../../../web/uploads/photos/products/'.$type.'/';
        FileTools::createDirIfNotExist($path);
        return $path;
    }

    public static function getBrandLogoPath($type = self::LOGO_TYPE_ORIGINAL, $absolute = true)
    {
        $path = ($absolute ? __DIR__.'/../../../../web/' : '').'uploads/brands/'.$type.'/';
        FileTools::createDirIfNotExist($path);
        return $path;
    }

    /**
     * Create dir if path dirs not exist
     *
     * @param $path
     */
    private static function createDirIfNotExist($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }
}