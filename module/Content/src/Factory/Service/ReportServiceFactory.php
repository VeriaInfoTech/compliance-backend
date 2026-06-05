<?php

namespace Content\Factory\Service;

use Content\Service\ItemService;
use Content\Service\ReportService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Pi\Core\Service\UtilityService;

class ReportServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return ReportService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ReportService
    {
        return new ReportService(
            $container->get(ItemService::class),
            $container->get(UtilityService::class)
        );
    }
}
