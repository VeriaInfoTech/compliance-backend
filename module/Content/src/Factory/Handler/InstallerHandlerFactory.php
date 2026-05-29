<?php

namespace Content\Factory\Handler;

use Content\Handler\InstallerHandler;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class InstallerHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): InstallerHandler
    {
        return new InstallerHandler(
            $container->get(InstallerService::class)
        );
    }
}
