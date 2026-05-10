<?php

namespace Pi\Notification\Factory\Sender\SMS;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Notification\Sender\SMS\Twilio;
use Psr\Container\ContainerInterface;

class TwilioFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return Twilio
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Twilio
    {
        return new Twilio();
    }
}