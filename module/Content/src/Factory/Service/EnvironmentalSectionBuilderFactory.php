<?php

namespace Content\Factory\Service;

use Content\Service\EnvironmentalSectionBuilder;
use Content\Service\ReportDataMapper;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class EnvironmentalSectionBuilderFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): EnvironmentalSectionBuilder
    {
        return new EnvironmentalSectionBuilder(
            $container->get(ReportDataMapper::class)
        );
    }
}
