<?php

namespace Pi\Notification\Factory\Sender\Mail;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Notification\Sender\Mail\PhpMail;
use Psr\Container\ContainerInterface;

class PhpMailFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return PhpMail
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): PhpMail
    {
        return new PhpMail();
    }
}