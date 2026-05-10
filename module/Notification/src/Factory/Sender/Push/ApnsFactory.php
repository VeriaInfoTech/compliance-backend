<?php

namespace Pi\Notification\Factory\Sender\Push;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Notification\Sender\Push\Apns;
use Psr\Container\ContainerInterface;

class ApnsFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return Apns
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Apns
    {
        $config = $container->get('config');
        return new Apns($config['notification']['push']['apns']);
    }
}