<?php

namespace Erm\Factory\Handler\Api\Insurance;

use Erm\Handler\Api\Compliance\ComplianceDashboardHandler;
use Erm\Handler\Api\Compliance\CompliancePerformanceReportHandler;
use Erm\Handler\Api\Compliance\ComplianceProgressHandler;
use Erm\Handler\Api\Insurance\InsuranceDashboardHandler;
use Erm\Service\TaskService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
 use Pi\User\Service\RoleService;

class InsuranceDashboardHandlerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return InsuranceDashboardHandler
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null):InsuranceDashboardHandler
    {
        return new InsuranceDashboardHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(TaskService::class),
            $container->get(RoleService::class)
        );
    }
}