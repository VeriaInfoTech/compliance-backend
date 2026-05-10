<?php

namespace Erm\Factory\Handler\Api\Maturity;

use Erm\Handler\Api\Compliance\ComplianceDashboardHandler;
use Erm\Handler\Api\Compliance\CompliancePerformanceReportHandler;
use Erm\Handler\Api\Compliance\ComplianceProgressHandler;
use Erm\Handler\Api\Maturity\MaturityDashboardHandler;
use Erm\Service\TaskService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
 use Pi\User\Service\RoleService;

class MaturityDashboardHandlerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return MaturityDashboardHandler
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null):MaturityDashboardHandler
    {
        return new MaturityDashboardHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(TaskService::class),
            $container->get(RoleService::class)
        );
    }
}