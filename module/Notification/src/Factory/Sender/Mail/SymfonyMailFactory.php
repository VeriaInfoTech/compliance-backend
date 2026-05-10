<?php

namespace Pi\Notification\Factory\Sender\Mail;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Notification\Sender\Mail\SymfonyMail;
use Psr\Container\ContainerInterface;

class SymfonyMailFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return SymfonyMail
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SymfonyMail
    {
        return new SymfonyMail();
    }
}