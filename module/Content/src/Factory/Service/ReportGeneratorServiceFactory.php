<?php

namespace Content\Factory\Service;

use Content\Service\EnvironmentalSectionBuilder;
use Content\Service\GovernanceSectionBuilder;
use Content\Service\ReportDataMapper;
use Content\Service\ReportGeneratorService;
use Content\Service\SocialSectionBuilder;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ReportGeneratorServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ReportGeneratorService
    {
        return new ReportGeneratorService(
            $container->get(ReportDataMapper::class),
            $container->get(EnvironmentalSectionBuilder::class),
            $container->get(SocialSectionBuilder::class),
            $container->get(GovernanceSectionBuilder::class)
        );
    }
}
