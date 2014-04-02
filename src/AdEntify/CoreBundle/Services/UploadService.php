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
use AdEntify\CoreBundle\Util\TaskProgressHelper;
use Guzzle\Service\Client;
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

    public function uploadPhotos($user, $data, $task = null)
    {
        $venueRepository = $this->em->getRepository('AdEntifyCoreBundle:Venue');
        $categories = $this->em->getRepository('AdEntifyCoreBundle:Category')->findAll();

        if ($data->images) {
            $images = $data->images;
            $source = $data->source ? $data->source : '';
            $countUploadedPhotos = $failedPhotos = 0;
            $uploadedPhotos = array();
            $venues = array();
            if ($task) {
                $taskProgressHelper = new TaskProgressHelper($this->em);
                $taskProgressHelper->start($task, (count($images) * 3));
            }

            $client = new Client();
            $requests = array();
            // Download images in parallel
            foreach($images as $image) {
                $requests[] = $client->get($image->originalSource);
            }
            $responses = $client->send($requests);

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
                    if (isset($taskProgressHelper))
                        $taskProgressHelper->advance(2);
                }
                // Online photos, download them
                else {
                    // ORIGINAL IMAGE

                    // Get downloaded image
                    $downloadedImage = null;
                    foreach($responses as $response) {
                        if ($response->getEffectiveUrl() == $image->originalSource)
                            $downloadedImage = $response;
                    }
                    if ($downloadedImage)
                        $originalUrl = $this->fileUploader->uploadFromContent($downloadedImage->getBody(), $downloadedImage->getContentType(), $originalPath, $filename);
                    else
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

                    if (isset($taskProgressHelper))
                        $taskProgressHelper->advance();

                    // Set url to the downloaded image or the source url if not download
                    if ($originalUrl) {
                        $photo->setOriginalUrl($originalUrl);
                        $thumb->setOriginalPath($originalUrl);
                        $uploadedPhotos[] = $photo;
                        $countUploadedPhotos++;

                        $thumb->addThumbSize(FileTools::PHOTO_TYPE_SMALLL);
                        $this->generateThumbIfOriginalLarger($thumb, self::MEDIUM_SIZE, FileTools::PHOTO_TYPE_MEDIUM, $photo);
                        $this->generateThumbIfOriginalLarger($thumb, self::LARGE_SIZE, FileTools::PHOTO_TYPE_LARGE, $photo);

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

                        if (isset($taskProgressHelper))
                            $taskProgressHelper->advance();

                        $photo->setStatus(Photo::STATUS_READY);
                    } else {
                        // Set status to LOAD_ERROR if original image is not reachable
                        $photo->setOriginalUrl($image->originalSource);
                        $photo->setStatus(Photo::STATUS_LOAD_ERROR);
                        $failedPhotos++;
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
                    foreach(array_unique($image->hashtags) as $hashtagName) {
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

                if (!empty($image->title))
                    $photo->setCaption($image->title);

                if (!empty($source))
                    $photo->setSource($source);
                $this->em->persist($photo);

                if (isset($taskProgressHelper))
                    $taskProgressHelper->advance();
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
            // Original larger, add it to generate the thumb
            $thumb->addThumbSize($photoType);
        }
    }
}