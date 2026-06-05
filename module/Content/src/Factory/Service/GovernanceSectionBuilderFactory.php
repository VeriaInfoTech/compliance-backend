<?php

namespace Content\Factory\Service;

use Content\Service\GovernanceSectionBuilder;
use Content\Service\ReportDataMapper;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class GovernanceSectionBuilderFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): GovernanceSectionBuilder
    {
        return new GovernanceSectionBuilder(
            $container->get(ReportDataMapper::class)
        );
    }
}
