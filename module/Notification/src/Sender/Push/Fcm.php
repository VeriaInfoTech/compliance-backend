<?php

namespace Pi\Notification\Sender\Push;

use Google\Auth\Credentials\ServiceAccountCredentials;
use Google\Auth\HttpHandler\HttpHandlerFactory;

class Fcm implements PushInterface
{

    protected array $config;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function send($config, $params): void
    {
        ///TODO: handle for dubaytops apps
        //if($config==null&&empty($config)){
        //    $config = $this->config;
        //}
        //$config       = $config['fcm'];
        //$url          = $config['url'];
        //$token        = $params['device_token'];
        //$serverKey    = $config['server_key'];
        //$title        = $this->utility($params['title']);
        //$in_app_title = $this->utility($params['in_app_title']);
        //$body         = $this->utility($params['body']);
        //$in_app_body  = $this->utility($params['body']);
        //$type         = $params['type'];
        //$in_app       = $params['in_app'];
        //$image_url    = $params['image_url'];
        //$event        = $params['event'];
        //$aps          = ['mutable-content' => 1];
        //$payload      = ['aps' => $aps];
        //$fcm_options  = ['image' => $image_url];
        //$apns         = ['payload' => $payload, 'fcm_options' => $fcm_options];

        //$notification = [
        //    'title'             => $title,
        //    'content_available' => true,
        //    'mutable_content'   => true,
        //    'body'              => $body,
        //    'image'             => $image_url,
        //    'sound'             => 'default',
        //    'badge'             => '1',
        //];

        //$notData = [
        //    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
        //    'type'         => $type,
        //    'in_app_body'  => $in_app_body,
        //    'in_app'       => $in_app,
        //    'in_app_title' => $in_app_title,
        //    'event'        => $event,
        //    'image_url'    => $image_url,
        //];

        //$has_data = true;
        //if (isset($params['custom_information'])) {
        //    $notData['custom_information'] = $params['custom_information'];
        //}
        //$arrayToSend = ['to' => $token, 'data' => $notData, 'apns' => $apns, 'priority' => 'high'];
        //$arrayToSend['notification'] = $notification;
        //$json      = json_encode($arrayToSend);


        // Path to your downloaded service account JSON file
        $jsonKeyFilePath = $this->config['push']['fcm']['key_file_path'];

        // Check if the file exists
        if (!file_exists($jsonKeyFilePath)) {
            die("Service account file does not exist: " . $jsonKeyFilePath);
        }

        // Define the required scope for Firebase Cloud Messaging
        $scopes = ['https://www.googleapis.com/auth/firebase.messaging'];

        // Create ServiceAccountCredentials
        $credentials = new ServiceAccountCredentials($scopes, $jsonKeyFilePath);

        // Create the HTTP handler callable
        $httpHandler = HttpHandlerFactory::build();

        // Fetch the OAuth 2 access token
        $accessTokenArray = $credentials->fetchAuthToken($httpHandler);
        $accessToken      = $accessTokenArray['access_token'];


        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json',
        ];

        $arrayToSend = [
            'message' => [
                'token'        => $params['device_token'], // Device token for Android/iOS
                'notification' => [
                    'title' => $this->utility($params['title']),
                    'body'  => $this->utility($params['body']),
                    'image' => $params['image_url'],
                ],
                'data'         => [
                    'click_action'       => 'FLUTTER_NOTIFICATION_CLICK',
                    'type'               => $params['type'],
                    'in_app_title'       => $this->utility($params['in_app_title']),
                    'in_app_body'        => $this->utility($params['body']),
                    'in_app'             => (string)$params['in_app'],
                    'event'              => $params['event'],
                    'image_url'          => $params['image_url'],
                    'custom_information' => json_encode($params['custom_information']) ?? null,
                ],
                'apns'         => [
                    'headers'     => [
                        'apns-priority' => '10',  // High priority to wake the app
                        //'apns-push-type' => 'voip',  // VoIP push notification
                    ],
                    'payload'     => [
                        'aps' => [
                            'content-available' => 1,  // Silent push to wake the app
                            'sound'             => 'default',  // Optional sound for call notification
                            'mutable-content'   => 1,  // For media
                        ],
                    ],
                    'fcm_options' => [
                        'image' => $params['image_url'],  // Optional image URL for call notification
                    ],
                ],
                'android'      => [
                    'priority'     => 'high',
                    'notification' => [
                        'title' => $this->utility($params['title']),
                        'body'  => $this->utility($params['body']),
                        'icon'  => 'default',  // Set a default icon
                        'image' => $params['image_url'],
                        'sound' => 'default',
                    ],
                    'data'         => [
                        'click_action'       => 'FLUTTER_NOTIFICATION_CLICK',
                        'type'               => $params['type'],
                        'event'              => $params['event'],
                        'in_app_title'       => $this->utility($params['in_app_title']),
                        'in_app_body'        => $this->utility($params['body']),
                        'custom_information' => json_encode($params['custom_information']) ?? null,
                    ],
                ],
            ],
        ];


        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->config['push']['fcm']['url']);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arrayToSend));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
    }

    private function utility($text): string
    {
        if (isset($this->config['push']['setting'])) {
            $limit = $this->config['push']['setting']['limitation'];
            $xss   = $this->config['push']['setting']['xss'];
            $text  = $limit['status'] ? ((strlen($text) > $limit['length']) ? substr($text, 0, $limit['length']) . '...' : $text) : $text;
            $text  = $xss['status'] ? htmlspecialchars($text, ENT_QUOTES, 'UTF-8') : $text;
        }

        return $text;
    }
}