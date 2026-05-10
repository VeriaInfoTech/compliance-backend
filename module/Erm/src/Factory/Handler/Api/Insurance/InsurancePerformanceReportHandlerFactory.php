<?php

namespace Erm\Factory\Handler\Api\Insurance;

use Erm\Handler\Api\Insurance\InsurancePerformanceReportHandler;
use Erm\Service\TaskService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
 use Pi\User\Service\RoleService;

class InsurancePerformanceReportHandlerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null       $options
     *
     * @return InsurancePerformanceReportHandler
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null):InsurancePerformanceReportHandler
    {
        return new InsurancePerformanceReportHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(TaskService::class),
            $container->get(RoleService::class)
        );
    }
}