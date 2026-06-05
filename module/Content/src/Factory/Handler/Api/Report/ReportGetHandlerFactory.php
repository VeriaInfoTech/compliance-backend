<?php

namespace Content\Factory\Handler\Api\Report;

use Content\Handler\Api\Report\ReportGetHandler;
use Content\Service\ItemService;
use Content\Service\ReportGeneratorService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ReportGetHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ReportGetHandler
    {
        return new ReportGetHandler(
            $container->get(ItemService::class),
            $container->get(ReportGeneratorService::class)
        );
    }
}

