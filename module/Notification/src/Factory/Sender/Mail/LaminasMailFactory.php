<?php

namespace Pi\Notification\Factory\Sender\Mail;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Notification\Sender\Mail\LaminasMail;
use Psr\Container\ContainerInterface;

class LaminasMailFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return LaminasMail
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LaminasMail
    {
        return new LaminasMail();
    }
}