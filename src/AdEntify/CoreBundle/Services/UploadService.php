<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 22/07/2013
 * Time: 16:03
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Services;

use AdEntify\CoreBundle\Entity\Photo;
use AdEntify\CoreBundle\Entity\Tag;
use AdEntify\CoreBundle\Entity\User;
use AdEntify\CoreBundle\Entity\Venue;
use AdEntify\CoreBundle\Model\Thumb;
use AdEntify\CoreBundle\Util\FileTools;
use Symfony\Component\HttpFoundation\Response;

class UploadService
{
    const SMALL_SIZE = 150;
    const MEDIUM_SIZE = 320;
    const LARGE_SIZE = 1024;

    protected $em;
    protected $thumbService;
    protected $rootUrl;

    public function __construct($em, ThumbService $thumbService, $rootUrl) {
        $this->em = $em;
        $this->thumbService = $thumbService;
        $this->rootUrl = $rootUrl;
    }

    public function uploadPhotos($user, $data)
    {
        $response = null;
        $venueRepository = $this->em->getRepository('AdEntifyCoreBundle:Venue');
        $categories = $this->em->getRepository('AdEntifyCoreBundle:Category')->findAll();

        if ($data->images) {
            $images = $data->images;
            $source = $data->source ? $data->source : '';
            $uploadedPhotos = $failedPhotos = 0;
            $venues = array();

            foreach($images as $image) {
                // Build filename & path
                $filename = uniqid().FileTools::getExtensionFromUrl($image->originalSource);
                $originalPath = FileTools::getUserPhotosPath($user, FileTools::PHOTO_TYPE_ORIGINAL);

                // Create a new photo
                $photo = new Photo();
                $photo->setOwner($user);
                if (isset($image->id))
                    $photo->setPhotoSourceId($image->id);
                // Set visibility scope
                if (isset($image->confidentiality) && $image->conffidentiality == 'private')
                    $photo->setVisibilityScope(Photo::SCOPE_PRIVATE);
                else
                    $photo->setVisibilityScope(Photo::SCOPE_PUBLIC);
                // Set category(ies)
                if (isset($image->categories) && is_array($image->categories) && count($image->categories) > 0) {
                    foreach($image->categories as $categoryId) {
                        foreach($categories as $category) {
                            if ($category->getId() == $categoryId) {
                                $photo->addCategory($category);
                                break;
                            }
                        }
                    }
                }

                // Photo Place
                switch ($source) {
                    case 'facebook':
                        if (isset($image->place) && isset($image->place->id) && isset($image->place->name)
                            && isset($image->place->location) && isset($image->place->location->latitude)
                            && isset($image->place->location->longitude)) {
                            $venue = $venueRepository->findOneBy(array(
                                'facebookId' => $image->place->id
                            ));
                            // Venue found, link to photo
                            if ($venue) {
                                $photo->setVenue($venue);
                            } else {
                                $found = false;
                                foreach($venues as $fbVenue) {
                                    if ($fbVenue == $image->place->id) {
                                        $photo->setVenue($fbVenue);
                                        $found = true;
                                        break;
                                    }
                                }
                                if (!$found) {
                                    $venue = new Venue();
                                    $venue->setFacebookId($image->place->id)
                                        ->setName($image->place->name)
                                        ->setLat($image->place->location->latitude)
                                        ->setLng($image->place->location->longitude);
                                    if (isset($image->place->location->street)) {
                                        $venue->setAddress($image->place->location->street);
                                    }
                                    if (isset($image->place->location->city)) {
                                        $venue->setCity($image->place->location->city);
                                    }
                                    if (isset($image->place->location->state)) {
                                        $venue->setState($image->place->location->state);
                                    }
                                    if (isset($image->place->location->country)) {
                                        $venue->setCountry($image->place->location->country);
                                    }
                                    if (isset($image->place->location->zip)) {
                                        $venue->setPostalCode($image->place->location->zip);
                                    }
                                    $photo->setVenue($venue);
                                    $this->em->persist($venue);
                                    $venues[] = $venue;
                                }
                            }
                            $photo->setLatitude($image->place->location->latitude);
                            $photo->setLongitude($image->place->location->longitude);
                        }
                        break;
                    case 'instagram':
                        if (isset($image->location) && isset($image->location->latitude)
                            && isset($image->location->longitude)) {
                            $photo->setLatitude($image->location->latitude);
                            $photo->setLongitude($image->location->longitude);
                        }
                        break;
                }

                // Photo tags
                if (isset($image->tags) && is_array($image->tags) && count($image->tags) > 0) {
                    $personRepository = $this->em->getRepository('AdEntifyCoreBundle:Person');
                    foreach($image->tags as $tag) {
                        if (isset($tag->id) && isset($tag->y) && isset($tag->x)) {
                            // Search the person
                            $person = $personRepository->findOneBy(array(
                                'facebookId' => $tag->id
                            ));
                            // If found, add a new tag to the photo
                            if ($person) {
                                $t = new Tag();
                                $t->setType(Tag::TYPE_PERSON)->setLink('https://www.facebook.com/'.$tag->id)
                                    ->setPerson($person)->setPhoto($photo)->setTitle($tag->name)
                                    ->setXPosition($tag->x / 100)->setYPosition($tag->y / 100)->setOwner($user);
                                $photo->addTag($t);
                                $this->em->persist($t);
                            }
                        }
                    }
                }

                // Thumb
                $thumb = new Thumb();

                if ($source == 'local') {
                    $photo->setOriginalUrl($image->originalSource);
                    $photo->setOriginalWidth($image->originalWidth);
                    $photo->setOriginalHeight($image->originalHeight);

                    $photo->setSmallUrl($image->smallSource);
                    $photo->setSmallWidth($image->smallWidth);
                    $photo->setSmallHeight($image->smallHeight);

                    $photo->setMediumUrl($image->mediumSource);
                    $photo->setMediumWidth($image->mediumWidth);
                    $photo->setMediumHeight($image->mediumHeight);

                    $photo->setLargeUrl($image->largeSource);
                    $photo->setLargeWidth($image->largeWidth);
                    $photo->setLargeHeight($image->largeHeight);

                    $photo->setStatus(Photo::STATUS_READY);

                    $uploadedPhotos++;
                }
                // Online photos, download them
                else {
                    // ORIGINAL IMAGE
                    $originalStatus = $this->downloadImage($image->originalSource, $originalPath, $filename, 30);
                    // Get image size
                    if (empty($image->originalWidth) || empty($image->originalHeight)) {
                        // If image downloaded well, get imagesize
                        if (end($originalStatus) !== false) {
                            $size = getimagesize($originalPath.$filename);
                            $photo->setOriginalWidth($size[0]);
                            $photo->setOriginalHeight($size[1]);
                        }
                    } else {
                        $photo->setOriginalWidth($image->originalWidth);
                        $photo->setOriginalHeight($image->originalHeight);
                    }
                    // Set url to the downloaded image or the source url if not download
                    if ($originalStatus !== false) {
                        $photo->setOriginalUrl($this->rootUrl . 'uploads/photos/users/' . $user->getId(). '/original/' . $filename);
                        $thumb->setOriginalPath($originalPath.$filename);
                        $uploadedPhotos++;

                        // GET SMALL IMAGE
                        if (array_key_exists('smallSource', $image)) {
                            $smallPath = $originalPath = FileTools::getUserPhotosPath($user, FileTools::PHOTO_TYPE_SMALLL);
                            $status = $this->downloadImage($image->smallSource, $smallPath, $filename);

                            if ($status !== false) {
                                $photo->setSmallUrl($this->rootUrl . 'uploads/photos/users/' . $user->getId(). '/small/' . $filename);
                                // Set image size
                                if (empty($image->smallWidth) || empty($image->smallHeight)) {
                                    $size = getimagesize($smallPath.$filename);
                                    $photo->setSmallWidth($size[0]);
                                    $photo->setSmallHeight($size[1]);
                                } else {
                                    // Check if width is right
                                    if ($image->smallWidth != self::SMALL_SIZE) {
                                        $thumb->addThumbSize(FileTools::PHOTO_TYPE_SMALLL);
                                    } else {
                                        $photo->setSmallWidth($image->smallWidth);
                                        $photo->setSmallHeight($image->smallHeight);
                                    }
                                }
                            } else {
                                // Unable to load image, we have to generate it
                                $thumb->addThumbSize(FileTools::PHOTO_TYPE_SMALLL);
                            }
                        } else {
                            $thumb->addThumbSize(FileTools::PHOTO_TYPE_SMALLL);
                        }

                        // MEDIUM IMAGE
                        if (array_key_exists('mediumSource', $image)) {
                            $mediumPath = $originalPath = FileTools::getUserPhotosPath($user, FileTools::PHOTO_TYPE_MEDIUM);
                            $status = $this->downloadImage($image->mediumSource, $mediumPath, $filename);

                            if ($status !== false) {
                                $photo->setMediumUrl($this->rootUrl . 'uploads/photos/users/' . $user->getId(). '/medium/' . $filename);
                                // Set image size
                                if (empty($image->mediumWidth) || empty($image->mediumHeight)) {
                                    $size = getimagesize($mediumPath.$filename);
                                    $photo->setMediumWidth($size[0]);
                                    $photo->setMediumHeight($size[1]);
                                } else {
                                    // Check if width is right
                                    if ($image->mediumWidth != self::MEDIUM_SIZE) {
                                        $this->generateThumbIfOriginalLarger($thumb, self::MEDIUM_SIZE, FileTools::PHOTO_TYPE_MEDIUM, $photo, $user);
                                    } else {
                                        $photo->setMediumWidth($image->mediumWidth);
                                        $photo->setMediumHeight($image->mediumHeight);
                                    }
                                }
                            } else {
                                $this->generateThumbIfOriginalLarger($thumb, self::MEDIUM_SIZE, FileTools::PHOTO_TYPE_MEDIUM, $photo, $user);
                            }
                        } else {
                            $this->generateThumbIfOriginalLarger($thumb, self::MEDIUM_SIZE, FileTools::PHOTO_TYPE_MEDIUM, $photo, $user);
                        }

                        // LARGE IMAGE
                        if (array_key_exists('largeSource', $image)) {
                            $largePath = $originalPath = FileTools::getUserPhotosPath($user, FileTools::PHOTO_TYPE_LARGE);
                            $status = $this->downloadImage($image->largeSource, $largePath, $filename);

                            if ($status !== false) {
                                $photo->setLargeUrl($this->rootUrl . 'uploads/photos/users/' . $user->getId(). '/large/' . $filename);
                                // Set image size
                                if (empty($image->largeWidth) || empty($image->largeHeight)) {
                                    $size = getimagesize($largePath.$filename);
                                    $photo->setLargeWidth($size[0]);
                                    $photo->setLargeHeight($size[1]);
                                } else {
                                    $photo->setLargeWidth($image->largeWidth);
                                    $photo->setLargeHeight($image->largeHeight);
                                }
                            } else {
                                $this->generateThumbIfOriginalLarger($thumb, self::LARGE_SIZE, FileTools::PHOTO_TYPE_LARGE, $photo, $user);
                            }
                        } else {
                            $this->generateThumbIfOriginalLarger($thumb, self::LARGE_SIZE, FileTools::PHOTO_TYPE_LARGE, $photo, $user);
                        }

                        // Thumb generation
                        if ($thumb->IsThumbGenerationNeeded()) {
                            $generatedThumbs = $this->thumbService->generateUserPhotoThumb($thumb, $user, $filename);
                            foreach($generatedThumbs as $key => $value) {
                                switch ($key) {
                                    case FileTools::PHOTO_TYPE_LARGE:
                                        $photo->setLargeUrl($this->rootUrl . 'uploads/photos/users/' . $user->getId(). '/large/' . $value['filename']);
                                        $photo->setLargeWidth($value['width']);
                                        $photo->setLargeHeight($value['height']);
                                        break;
                                    case FileTools::PHOTO_TYPE_MEDIUM:
                                        $photo->setMediumUrl($this->rootUrl . 'uploads/photos/users/' . $user->getId(). '/medium/' . $value['filename']);
                                        $photo->setMediumWidth($value['width']);
                                        $photo->setMediumHeight($value['height']);
                                        break;
                                    case FileTools::PHOTO_TYPE_SMALLL:
                                        $photo->setSmallUrl($this->rootUrl . 'uploads/photos/users/' . $user->getId(). '/small/' . $value['filename']);
                                        $photo->setSmallWidth($value['width']);
                                        $photo->setSmallHeight($value['height']);
                                        break;
                                }
                            }
                        }

                        $photo->setStatus(Photo::STATUS_READY);
                    } else {
                        // Set status to LOAD_ERROR if original image is not reachable
                        $photo->setOriginalUrl($image->originalSource);
                        $photo->setStatus(Photo::STATUS_LOAD_ERROR);
                        $failedPhotos++;
                    }
                }

                if (!empty($image->title))
                    $photo->setCaption($image->title);

                if (!empty($source))
                    $photo->setSource($source);
                $this->em->persist($photo);
            }

            $this->em->flush();

            return array(
                'uploaded_images' => $uploadedPhotos,
                'failed_images' => $failedPhotos
            );
        } else {
            return array(
                'error' => 'No images posted.'
            );
        }
    }

    private function generateThumbIfOriginalLarger(Thumb $thumb, $size, $photoType, Photo $photo, User $user)
    {
        // if original size is smaller, copy original image and set url, width and height
        if ($photo->getOriginalWidth() < $size) {
            $sourceImage = FileTools::getUserPhotosPath($user, FileTools::PHOTO_TYPE_ORIGINAL) . $photo->getOriginalUrl();
            $destinationImage = FileTools::getUserPhotosPath($user, $photoType) . $photo->getOriginalUrl();
            copy($sourceImage, $destinationImage);

            if ($size == self::MEDIUM_SIZE) {
                $photo->setMediumUrl($photo->getOriginalUrl());
                $photo->setMediumWidth($photo->getOriginalWidth());
                $photo->setMediumHeight($photo->getOriginalHeight());
            } else if ($size == self::LARGE_SIZE) {
                $photo->setLargeUrl($photo->getOriginalUrl());
                $photo->setLargeWidth($photo->getOriginalWidth());
                $photo->setLargeHeight($photo->getOriginalHeight());
            }
        } else {
            // Unable to load image so add we generate it
            $thumb->addThumbSize($photoType);
        }
    }

    private function downloadImage($url, $originalPath, $filename, $timeout = 10)
    {
        $ch = curl_init($url);
        $fp = fopen($originalPath.$filename, 'wb');
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $status = curl_exec($ch);
        curl_close($ch);
        fclose($fp);

        return $status;
    }
}