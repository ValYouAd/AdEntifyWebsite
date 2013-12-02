<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 29/11/2013
 * Time: 15:28
 */

namespace AdEntify\CoreBundle\Services;


use AdEntify\CoreBundle\Util\FileTools;
use Gaufrette\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private $filesystem;
    private $baseUrl;

    public function __construct(Filesystem $filesystem, $baseUrl = '')
    {
        $this->filesystem = $filesystem;
        $this->baseUrl = $baseUrl;
    }

    /**
     * Upload file from UploadedFile
     *
     * @param UploadedFile $file
     * @param $path
     * @param $filename
     * @return string
     */
    public function upload(UploadedFile $file, $path, $filename)
    {
        // Format filename with path
        $uploadedFilename = $path.$filename;

        // Upload
        $this->write($uploadedFilename, file_get_contents($file->getPathname()), $file->getClientMimeType());

        return $this->baseUrl . $uploadedFilename;
    }

    /**
     * Upload file with content
     *
     * @param $content
     * @param $contentType
     * @param $path
     * @param $filename
     * @return string
     */
    public function uploadFromContent($content, $contentType, $path, $filename)
    {
        // Format filename with path
        $uploadedFilename = $path.$filename;

        // Upload
        $this->write($uploadedFilename, $content, $contentType);

        return $this->baseUrl . $uploadedFilename;
    }

    /**
     * Upload file from URL
     *
     * @param $url
     * @param $path
     * @return string
     */
    public function uploadFromUrl($url, $path, $timeout = 10)
    {
        // Get content and content-type of the file
        $result = FileTools::loadFile($url, $timeout);

        // Format filename with path
        $uploadedFilename = $path . uniqid() . FileTools::getExtensionFromUrl($url);

        // Upload
        $this->write($uploadedFilename, $result['content'], $result['content-type']);

        return $this->baseUrl . $uploadedFilename;
    }

    /**
     * Write file
     *
     * @param $filename
     * @param $content
     * @param $contentType
     */
    private function write($filename, $content, $contentType)
    {
        $adapter = $this->filesystem->getAdapter();
        $adapter->setMetadata($filename, array('contentType' => $contentType));
        $adapter->write($filename, $content);
    }
} 