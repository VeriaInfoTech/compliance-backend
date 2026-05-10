<?php

namespace Pi\Notification\Sender\SMS;

interface SMSInterface
{
    public function send($config, $params): void;
}