<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 04/04/2014
 * Time: 16:45
 */

namespace AdEntify\CoreBundle\Command;

use AdEntify\CoreBundle\Entity\Action;
use AdEntify\CoreBundle\Entity\Notification;
use AdEntify\CoreBundle\Entity\Task;
use AdEntify\CoreBundle\Model\Thumb;
use AdEntify\CoreBundle\Util\FileTools;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ThumbsCommand extends ContainerAwareCommand
{
    #region fields

    protected $em;
    protected $uploadService;
    protected $fileManager;
    protected $thumbService;

    #endregion

    protected function configure ()
    {
        $this->setName('adentify:thumbs:generate')
            ->setDescription('Generate thumbs if needed');
    }

    protected function execute (InputInterface $input, OutputInterface $output)
    {
        $this->setup();

        $batchSize = 20;
        $i = 0;
        $q = $this->em->createQuery('select p from AdEntifyCoreBundle:Photo p WHERE p.retinaUrl IS NULL OR p.source = :local ORDER BY p.id DESC')->setParameter('local', 'local');
        $iterableResult = $q->iterate();
        foreach($iterableResult AS $row) {
            $photo = $row[0];

            if (!$photo->getOriginalUrl())
                continue;

            $thumb = new Thumb();
            $thumb->setOriginalPath($photo->getOriginalUrl());

            $largeUrl = $photo->getLargeUrl();
            $mediumUrl = $photo->getMediumUrl();
            $smallUrl = $photo->getSmallUrl();
            $retinaUrl = $photo->getRetinaUrl();

            $thumb->configure($photo);

            try {
                // Thumb generation
                if ($thumb->IsThumbGenerationNeeded() && $photo->getOwner()) {
                    $generatedThumbs = $this->thumbService->generateUserPhotoThumb($thumb, $photo->getOwner(), uniqid().FileTools::getExtensionFromUrl($photo->getOriginalUrl()));

                    // Delete old files
                    $this->fileManager->deleteFromUrl($largeUrl);
                    $this->fileManager->deleteFromUrl($mediumUrl);
                    $this->fileManager->deleteFromUrl($smallUrl);
                    $this->fileManager->deleteFromUrl($retinaUrl);

                    foreach($generatedThumbs as $key => $value) {
                        switch ($key) {
                            case FileTools::PHOTO_SIZE_LARGE:
                                $photo->setLargeUrl($value['filename']);
                                $photo->setLargeWidth($value['width']);
                                $photo->setLargeHeight($value['height']);
                                break;
                            case FileTools::PHOTO_SIZE_RETINA:
                                $photo->setRetinaUrl($value['filename']);
                                $photo->setRetinaWidth($value['width']);
                                $photo->setRetinaHeight($value['height']);
                                break;
                            case FileTools::PHOTO_SIZE_MEDIUM:
                                $photo->setMediumUrl($value['filename']);
                                $photo->setMediumWidth($value['width']);
                                $photo->setMediumHeight($value['height']);
                                break;
                            case FileTools::PHOTO_SIZE_SMALLL:
                                $photo->setSmallUrl($value['filename']);
                                $photo->setSmallWidth($value['width']);
                                $photo->setSmallHeight($value['height']);
                                break;
                        }
                    }

                    $this->em->merge($photo);
                    $output->writeln('Thumbs updated');
                }
            } catch (\Exception $e) {
                $output->writeln($e->getMessage());
                $output->writeln($photo->getId());
                $output->writeln($photo->getOriginalUrl());
            }

            if (($i % $batchSize) == 0) {
                $this->em->flush(); // Executes all updates.
                $this->em->clear(); // Detaches all objects from Doctrine!
                $output->writeln('Flushed');
            }
            ++$i;
        }

        $this->em->flush(); // Executes all updates.
    }

    /**
     * Setup fields
     */
    private function setup()
    {
        $this->em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->uploadService = $this->getContainer()->get('ad_entify_core.upload');
        $this->fileManager = $this->getContainer()->get('adentify_storage.file_manager');
        $this->thumbService = $this->getContainer()->get('ad_entify_core.thumb');
    }
} 