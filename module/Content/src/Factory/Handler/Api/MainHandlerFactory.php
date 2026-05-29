<?php

namespace Content\Factory\Handler\Api;

use Content\Handler\Api\MainHandler;
use Content\Service\ItemService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class MainHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): MainHandler
    {
        return new MainHandler(
            $container->get(ItemService::class)
        );
    }
}
