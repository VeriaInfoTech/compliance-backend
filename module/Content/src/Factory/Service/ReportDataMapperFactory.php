<?php

namespace Content\Factory\Service;

use Content\Service\ReportDataMapper;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ReportDataMapperFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ReportDataMapper
    {
        return new ReportDataMapper();
    }
}
