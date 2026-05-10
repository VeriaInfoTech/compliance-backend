<?php

namespace Erm\Factory\Handler\Api\Risk;

use Erm\Handler\Api\Risk\RiskListHandler;
use Erm\Handler\Api\Risk\RiskPerformanceReportHandler;
use Erm\Handler\Api\Rule\RuleListHandler;
use Erm\Handler\Api\RuleHandler;
use Erm\Handler\Api\Task\TaskListHandler;
use Erm\Service\TaskService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Pi\User\Service\RoleService;

class RiskPerformanceReportHandlerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param mixed[]|null       $options
     *
     * @return RiskPerformanceReportHandler
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null):RiskPerformanceReportHandler
    {
        return new RiskPerformanceReportHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(TaskService::class),
            $container->get(RoleService::class)
        );
    }
}