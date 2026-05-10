<?php

namespace Pi\Notification\Factory\Sender\SMS;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Notification\Sender\SMS\KaveNegar;
use Psr\Container\ContainerInterface;

class KaveNegarFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return  KaveNegar
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): KaveNegar
    {
        return new  KaveNegar();
    }
}