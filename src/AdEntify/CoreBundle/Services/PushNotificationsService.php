<?php
/**
 * Created by PhpStorm.
 * User: pierrickmartos
 * Date: 15/09/2014
 * Time: 15:28
 */

namespace AdEntify\CoreBundle\Services;

use AdEntify\CoreBundle\Entity\DeviceRepository;
use Guzzle\Http\Client;
use Guzzle\Plugin\CurlAuth\CurlAuthPlugin;

class PushNotificationsService
{
    protected $apiKey;
    protected $client;
    protected $entityManager;
    const URI = 'http://push.pikilabs.com/api/fr/iphone/notification.json';

    public function __construct($apiKey, Client $client, $em)
    {
        $this->apiKey = $apiKey;
        $this->client = $client;
        $this->entityManager = $em;
    }

    /**
     * @param $user
     * @param $content
     * @param int $badge
     * @param string $operatingSystem
     * @return bool
     */
    public function sendToUser($user, $options, $operatingSystem = DeviceRepository::OS_APPLE)
    {
        $devices = $this->entityManager->getRepository('AdEntifyCoreBundle:Device')->getDevicesByOS($operatingSystem, $user);
        if (count($devices) > 0) {
            foreach($devices as $device) {
                $options['token'] = $device->getToken();
            }
            return $this->send($options);
        } else
            return true;
    }

    /**
     * @param $content
     * @param null $devices
     * @param int $badge
     * @return array
     */
    public function getOptions($content, $customs = null, $devices = null, $badge = 1)
    {
        $options = array(
            'content' => $content
        );
        if (is_array($devices) && count($devices) > 0) {
            foreach($devices as $device) {
                $options['token'] = $device->getToken();
            }
        }
        if (is_numeric($badge) && $badge > 0)
            $options['badge'] = $badge;
        if (is_array($customs) && count($customs) > 0)
            $options['customs'] = array($customs);
        return $options;
    }

    /**
     * @param $content
     * @param null $token
     * @param null $badge
     * @return bool
     */
    public function send($options)
    {
        $authPlugin = new CurlAuthPlugin('adentify', 'adentify');
        $this->client->addSubscriber($authPlugin);

        $request = $this->client->post(self::URI, array(
            'api_key' => $this->apiKey
        ), json_encode(array(
            'notification' => $options
        )));
        $response = $this->client->send($request);

        /*echo json_encode(array(
            'notification' => $options
        ));die;*/

        echo $response->getBody();die;

        return $response->getStatusCode() == 200 ? true : false;
    }
} 