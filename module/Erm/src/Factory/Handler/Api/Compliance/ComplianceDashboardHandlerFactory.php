<?php

namespace Erm\Factory\Handler\Api\Compliance;

use Erm\Handler\Api\Compliance\ComplianceDashboardHandler;
use Erm\Handler\Api\Compliance\CompliancePerformanceReportHandler;
use Erm\Handler\Api\Compliance\ComplianceProgressHandler;
use Erm\Service\TaskService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
 use Pi\User\Service\RoleService;

class ComplianceDashboardHandlerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null       $options
     *
     * @return ComplianceDashboardHandler
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null):ComplianceDashboardHandler
    {
        return new ComplianceDashboardHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(TaskService::class),
            $container->get(RoleService::class)
        );
    }
}