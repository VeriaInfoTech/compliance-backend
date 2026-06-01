<?php

namespace Content\Factory\Handler\Api\Dashboard;

use Content\Handler\Api\Dashboard\DashboardGetHandler;
use Content\Service\ItemService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class DashboardGetHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): DashboardGetHandler
    {
        return new DashboardGetHandler(
            $container->get(ItemService::class)
        );
    }
}
