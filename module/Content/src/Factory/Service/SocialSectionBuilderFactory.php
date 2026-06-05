<?php

namespace Content\Factory\Service;

use Content\Service\ReportDataMapper;
use Content\Service\SocialSectionBuilder;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SocialSectionBuilderFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): SocialSectionBuilder
    {
        return new SocialSectionBuilder(
            $container->get(ReportDataMapper::class)
        );
    }
}
