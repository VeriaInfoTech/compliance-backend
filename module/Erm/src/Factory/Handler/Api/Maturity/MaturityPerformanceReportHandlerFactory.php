<?php

namespace Erm\Factory\Handler\Api\Maturity;

use Erm\Handler\Api\Maturity\MaturityPerformanceReportHandler;
use Erm\Service\TaskService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
 use Pi\User\Service\RoleService;

class MaturityPerformanceReportHandlerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null       $options
     *
     * @return MaturityPerformanceReportHandler
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null):MaturityPerformanceReportHandler
    {
        return new MaturityPerformanceReportHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(TaskService::class),
            $container->get(RoleService::class)
        );
    }
}