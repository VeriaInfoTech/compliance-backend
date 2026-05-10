<?php

namespace Pi\Notification\Factory\Sender\Push;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Notification\Sender\Push\Fcm;
use Psr\Container\ContainerInterface;

class FcmFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return Fcm
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): Fcm
    {
        $config = $container->get('config');
        return new Fcm($config['notification']);
    }
}