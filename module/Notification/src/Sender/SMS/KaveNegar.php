<?php

namespace Pi\Notification\Sender\SMS;

use Laminas\Soap\Client as LaminasSoapClient;

class KaveNegar implements SMSInterface
{
    public function send($config, $params): void
    {
        // Set SMS params
        $smsParams = [
            'userName' => $config['kave_negar']['username'],
            'password' => $config['kave_negar']['password'],
            'sender'   => $config['kave_negar']['number'],
            'receptor' => [str_replace('+98', '0', $params['mobile'])],
            'message'  => $params['message'],
            'unixdate' => 0,
            'msgmode'  => 1,
            'status'   => 1,
        ];

        // Send SMS
        $client = new LaminasSoapClient($config['kave_negar']['url']);
        $client->SendSimpleByLoginInfo($smsParams)->status;
    }
}