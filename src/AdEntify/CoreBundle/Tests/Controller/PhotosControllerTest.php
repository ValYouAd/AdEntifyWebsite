<?php

namespace AdEntify\CoreBundle\Tests\Controller;

use AdEntify\CoreBundle\Tests\EnhancedWebTestCase;

class PhotosControllerTest extends EnhancedWebTestCase
{
    private $options = array();
    private $photos = array();

    private function getPrivatePhoto()
    {
        $this->getClient();
        $this->photos['private'] = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->createQuery('SELECT photo.id
                           FROM AdEntify\CoreBundle\Entity\Photo photo
                           WHERE photo.visibilityScope = :private
                           ORDER BY photo.id DESC')
            ->setMaxResults(1)
            ->setParameters(array (
                'private' => 'private'
            ))->getSingleScalarResult();
    }

    private function getPublicPhoto()
    {
        $this->getClient();
        $this->photos['public'] = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager()
            ->createQuery('SELECT photo.id
                           FROM AdEntify\CoreBundle\Entity\Photo photo
                           ORDER BY photo.id DESC')
            ->setMaxResults(1)
            ->getSingleScalarResult();
    }

    public function __construct()
    {
        $this->options['key'] = null;
        $this->options['authorization'] = false;
        $this->options['http_code'] = 200;
        $this->options['has_pagination'] = false;
        $this->options['return_type'] = 'object';
        $this->getPublicPhoto();
        $this->getPrivatePhoto();
    }

    public function testCget()
    {
        $this->options['key'] = 'data';
        $this->options['has_pagination'] = true;
        for($i = 0; $i < 2; $i++)
        {
            $this->options['authorization'] = ($i) ? true : false;
            $this->assertAllGet('/api/v1/photos', $this->options);
        }
    }

    public function testGetAction()
    {
        $this->options['key'] = 'id';
        for ($i = 0; $i < 2; $i++) {
            $this->options['authorization'] = ($i) ? true : false;
            $photo = ($i) ? $this->photos['private'] : $this->photos['public'];
            $this->assertAllGet(sprintf('api/v1/photos/%s', $photo), $this->options);
        }
    }
}