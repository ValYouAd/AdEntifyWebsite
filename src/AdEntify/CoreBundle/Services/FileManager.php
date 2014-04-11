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

class FileManager
{
    private $filesystem;
    private $baseUrl;
    protected $bucketName;

    public function __construct(Filesystem $filesystem, $baseUrl = '', $bucketName)
    {
        $this->filesystem = $filesystem;
        $this->baseUrl = $baseUrl;
        $this->bucketName = $bucketName;
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
        // Upload
        $this->write($this->getPath($path, $filename), file_get_contents($file->getPathname()), $file->getClientMimeType());

        return $this->baseUrl . $this->getPath($path, $filename, true);
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
        // Upload
        $this->write($this->getPath($path, $filename), $content, $contentType);

        return $this->baseUrl . $this->getPath($path, $filename, true);
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
        $result = FileTools::loadRemoteFile($url, $timeout);

        // Format filename with path
        $uploadedFilename = $path . uniqid() . FileTools::getExtensionFromUrl($url);

        // Upload
        $this->write($uploadedFilename, $result['content'], $result['content-type']);

        return $this->baseUrl . $uploadedFilename;
    }

    /**
     * Delete file from URL
     *
     * @param $url
     * @return bool
     */
    public function deleteFromUrl($url) {
        $adapter = $this->filesystem->getAdapter();
        $pos = strpos($url, $this->bucketName);
        if ($pos !== false) {
            $pos = $pos + strlen($this->bucketName) + 1; // +1 to remove trailing slash
            if ($adapter->exists(substr($url, $pos, strlen($url) - $pos)))
                return $adapter->delete(substr($url, $pos, strlen($url) - $pos));
        }
        return false;
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

    private function getPath($path, $filename, $encode = false)
    {
        return $encode ? $path.rawurlencode($filename) : $path.$filename;
    }
} 