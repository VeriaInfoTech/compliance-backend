<?php

namespace Pi\Notification\Sender\Push;

interface PushInterface
{
    public function send($config, $params): void;
}