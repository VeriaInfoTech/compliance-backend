<?php

namespace Pi\Notification\Sender\Push;

use Pushok\AuthProvider\Token;
use Pushok\Client;
use Pushok\Notification;
use Pushok\Payload;
use Pushok\Payload\Alert;

class Apns implements PushInterface
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
        $deviceToken  = $params['device_token'];
        $authProvider = Token::create([
            'key_id'             => $this->config['key_id'],
            'team_id'            => $this->config['team_id'],
            'app_bundle_id'      => $this->config['app_bundle_id'],
            'private_key_path'   => $this->config['private_key_path'],
            'private_key_secret' => $this->config['private_key_secret'],
        ]);

        $alert = Alert::create()->setTitle($this->utility($params['title']));
        $alert = $alert->setBody($this->utility($params['body']));

        $payload = Payload::create()
            ->setAlert($alert)
            ->setPushType($params['push_type'])
            ->setSound('default')
            ->setContentAvailability(1);

        if (isset($params['custom_information'])) {
            $payload->setCustomValue('custom_information', $params['custom_information']);
        }

        $deviceTokens = [$deviceToken];

        $notifications = [];
        foreach ($deviceTokens as $deviceToken) {
            $notifications[] = new Notification($payload, $deviceToken);
        }

        $client = new Client($authProvider, $production = $this->config['is_production']);
        $client->addNotifications($notifications);
        $responses = $client->push();

    }

    private function utility($text): string
    {
        if (isset($this->config['setting'])) {
            $limit = $this->config['setting']['limitation'];
            $xss   = $this->config['setting']['xss'];
            $text  = $limit['status'] ? ((strlen($text) > $limit['length']) ? substr($text, 0, $limit['length']) . '...' : $text) : $text;
            $text  = $xss['status'] ? htmlspecialchars($text, ENT_QUOTES, 'UTF-8') : $text;
        }
        return $text;
    }
}