<?php
/**
 * Created by JetBrains PhpStorm.
 * User: pierrickmartos
 * Date: 22/07/2013
 * Time: 16:03
 * To change this template use File | Settings | File Templates.
 */

namespace AdEntify\CoreBundle\Services;

use AdEntify\CoreBundle\Entity\Person;
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
    protected $fbApi;
    protected $fileUploader;

    public function __construct($em, ThumbService $thumbService, $rootUrl, \BaseFacebook $fbApi, $fileUploader) {
        $this->em = $em;
        $this->thumbService = $thumbService;
        $this->rootUrl = $rootUrl;
        $this->fbApi = $fbApi;
        $this->fileUploader = $fileUploader;
    }

    public function uploadPhotos($user, $data)
    {
        $response = null;
        $venueRepository = $this->em->getRepository('AdEntifyCoreBundle:Venue');
        $categories = $this->em->getRepository('AdEntifyCoreBundle:Category')->findAll();

        if ($data->images) {
            $images = $data->images;
            $source = $data->source ? $data->source : '';
            $countUploadedPhotos = $failedPhotos = 0;
            $uploadedPhotos = array();
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
                if (isset($image->confidentiality) && $image->confidentiality == 'private')
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
                        if (isset($tag->id) && isset($tag->y) && isset($tag->x) && isset($tag->name)) {
                            $person = null;
                            $brand = null;
                            $fieldName = null;
                            $link = null;
                            switch ($source) {
                                case 'facebook':
                                    $fieldName = 'facebookId';
                                    $link = 'https://www.facebook.com/'.$tag->id;
                                    break;
                                case 'instagram':
                                    $fieldName = 'instagramId';
                                    $link = 'https://instagram.com/'.$tag->username;
                                    break;
                            }

                            if ($fieldName) {
                                $person = $personRepository->findOneBy(array(
                                    $fieldName => $tag->id
                                ));
                                if (!$person && $source == 'facebook')
                                    $brand = $this->em->getRepository('AdEntifyCoreBundle:Brand')->createOrUpdateBrandFromFacebookId($tag->id, $this->fbApi);
                            }

                            // If not found, check if its a brand
                            if (!$person && !$brand) {
                                $profilePicture = isset($tag->profilePicture) ? $tag->profilePicture : null;
                                $person = $personRepository->createAndLinkToExistingUser(null, null, $tag->name, $profilePicture, $tag->id, $source);
                            } else if ($person && !$brand) {
                                $profilePicture = $person->getProfilePictureUrl();
                                if (isset($tag->profilePicture) && empty($profilePicture)) {
                                    $person->setProfilePictureUrl($tag->profilePicture);
                                    $this->em->merge($person);
                                }
                            }

                            $t = new Tag();
                            if ($person) {
                                $t->setType(Tag::TYPE_PERSON)->setLink($link)
                                    ->setPerson($person)->setPhoto($photo)->setTitle($tag->name)
                                    ->setXPosition($tag->x / 100)->setYPosition($tag->y / 100)->setOwner($user);
                            } else if ($brand) {
                                $t->setType(Tag::TYPE_BRAND)->setLink($link)
                                    ->setBrand($brand)->setPhoto($photo)->setTitle($tag->name)
                                    ->setXPosition($tag->x / 100)->setYPosition($tag->y / 100)->setOwner($user);
                            }
                            $photo->addTag($t);
                            $this->em->persist($t);
                        }
                    }
                }

                // Photo hashtags
                if (isset($image->hashtags) && is_array($image->hashtags) && count($image->hashtags) > 0) {
                    $hashtagRepository = $this->em->getRepository('AdEntifyCoreBundle:Hashtag');
                    foreach($image->hashtags as $hashtagName) {
                        $hashtag = $hashtagRepository->createIfNotExist($hashtagName);
                        if ($hashtag) {
                            $found = false;
                            foreach($photo->getHashtags() as $ht) {
                                if ($ht->getId() == $hashtag->getId()) {
                                    $found = true;
                                    break;
                                }
                            }
                            if (!$found)
                                $photo->addHashtag($hashtag);
                        }
                    }
                } else if (!empty($image->title)) {
                    // Get hashtags in title
                    $pattern = '/(?:^|\s)(\#\w+)/';
                    preg_match_all($pattern, $image->title, $matches, PREG_OFFSET_CAPTURE);
                    if (count($matches[0]) > 0) {
                        $hashtagRepository = $this->em->getRepository('AdEntifyCoreBundle:Hashtag');
                        foreach($matches[0] as $match) {
                            $hashtag = $hashtagRepository->createIfNotExist(str_replace('#', '', $match[0]));
                            if ($hashtag)
                                $photo->addHashtag($hashtag);
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

                    $uploadedPhotos[] = $photo;
                    $countUploadedPhotos++;
                }
                // Online photos, download them
                else {
                    // ORIGINAL IMAGE
                    $originalUrl = $this->fileUploader->uploadFromUrl($image->originalSource, $originalPath, 30);
                    // Get image size
                    if (empty($image->originalWidth) || empty($image->originalHeight)) {
                        // If image downloaded well, get imagesize
                        if ($originalUrl) {
                            $size = getimagesize($originalUrl);
                            $photo->setOriginalWidth($size[0]);
                            $photo->setOriginalHeight($size[1]);
                        }
                    } else {
                        $photo->setOriginalWidth($image->originalWidth);
                        $photo->setOriginalHeight($image->originalHeight);
                    }
                    // Set url to the downloaded image or the source url if not download
                    if ($originalUrl) {
                        $photo->setOriginalUrl($originalUrl);
                        $thumb->setOriginalPath($originalUrl);
                        $uploadedPhotos[] = $photo;
                        $countUploadedPhotos++;

                        // GET SMALL IMAGE
                        if (array_key_exists('smallSource', $image)) {
                            $smallPath = $originalPath = FileTools::getUserPhotosPath($user, FileTools::PHOTO_TYPE_SMALLL);
                            $smalllUrl = $this->fileUploader->uploadFromUrl($image->smallSource, $smallPath, 30);

                            if ($smalllUrl) {
                                $photo->setSmallUrl($smalllUrl);
                                // Set image size
                                if (empty($image->smallWidth) || empty($image->smallHeight)) {
                                    $size = getimagesize($smalllUrl);
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
                            $mediumUrl = $this->fileUploader->uploadFromUrl($image->mediumSource, $mediumPath, 30);

                            if ($mediumUrl) {
                                $photo->setMediumUrl($mediumUrl);
                                // Set image size
                                if (empty($image->mediumWidth) || empty($image->mediumHeight)) {
                                    $size = getimagesize($mediumUrl);
                                    $photo->setMediumWidth($size[0]);
                                    $photo->setMediumHeight($size[1]);
                                } else {
                                    // Check if width is right
                                    if ($image->mediumWidth != self::MEDIUM_SIZE) {
                                        $this->generateThumbIfOriginalLarger($thumb, self::MEDIUM_SIZE, FileTools::PHOTO_TYPE_MEDIUM, $photo);
                                    } else {
                                        $photo->setMediumWidth($image->mediumWidth);
                                        $photo->setMediumHeight($image->mediumHeight);
                                    }
                                }
                            } else {
                                $this->generateThumbIfOriginalLarger($thumb, self::MEDIUM_SIZE, FileTools::PHOTO_TYPE_MEDIUM, $photo);
                            }
                        } else {
                            $this->generateThumbIfOriginalLarger($thumb, self::MEDIUM_SIZE, FileTools::PHOTO_TYPE_MEDIUM, $photo);
                        }

                        // LARGE IMAGE
                        if (array_key_exists('largeSource', $image)) {
                            $largePath = $originalPath = FileTools::getUserPhotosPath($user, FileTools::PHOTO_TYPE_LARGE);
                            $largeUrl = $this->fileUploader->uploadFromUrl($image->largeSource, $largePath, 30);

                            if ($largeUrl) {
                                $photo->setLargeUrl($largeUrl);
                                // Set image size
                                if (empty($image->largeWidth) || empty($image->largeHeight)) {
                                    $size = getimagesize($largeUrl);
                                    $photo->setLargeWidth($size[0]);
                                    $photo->setLargeHeight($size[1]);
                                } else {
                                    $photo->setLargeWidth($image->largeWidth);
                                    $photo->setLargeHeight($image->largeHeight);
                                }
                            } else {
                                $this->generateThumbIfOriginalLarger($thumb, self::LARGE_SIZE, FileTools::PHOTO_TYPE_LARGE, $photo);
                            }
                        } else {
                            $this->generateThumbIfOriginalLarger($thumb, self::LARGE_SIZE, FileTools::PHOTO_TYPE_LARGE, $photo);
                        }

                        // Thumb generation
                        if ($thumb->IsThumbGenerationNeeded()) {
                            $generatedThumbs = $this->thumbService->generateUserPhotoThumb($thumb, $user, $filename);
                            foreach($generatedThumbs as $key => $value) {
                                switch ($key) {
                                    case FileTools::PHOTO_TYPE_LARGE:
                                        $photo->setLargeUrl($value['filename']);
                                        $photo->setLargeWidth($value['width']);
                                        $photo->setLargeHeight($value['height']);
                                        break;
                                    case FileTools::PHOTO_TYPE_MEDIUM:
                                        $photo->setMediumUrl($value['filename']);
                                        $photo->setMediumWidth($value['width']);
                                        $photo->setMediumHeight($value['height']);
                                        break;
                                    case FileTools::PHOTO_TYPE_SMALLL:
                                        $photo->setSmallUrl($value['filename']);
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
                'uploaded_images' => $countUploadedPhotos,
                'photos' => $uploadedPhotos,
                'failed_images' => $failedPhotos
            );
        } else {
            return array(
                'error' => 'No images posted.'
            );
        }
    }

    private function generateThumbIfOriginalLarger(Thumb $thumb, $size, $photoType, Photo $photo)
    {
        if ($photo->getOriginalWidth() < $size) {
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
}