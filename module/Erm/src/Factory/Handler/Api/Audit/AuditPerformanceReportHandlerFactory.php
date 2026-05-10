<?php

namespace Erm\Factory\Handler\Api\Audit;

use Erm\Handler\Api\Audit\AuditPerformanceReportHandler;
use Erm\Service\TaskService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use  Pi\User\Service\RoleService;

class AuditPerformanceReportHandlerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null       $options
     *
     * @return AuditPerformanceReportHandler
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null):AuditPerformanceReportHandler
    {
        return new AuditPerformanceReportHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(TaskService::class),
            $container->get(RoleService::class)
        );
    }
}