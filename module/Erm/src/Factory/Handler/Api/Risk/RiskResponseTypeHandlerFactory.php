<?php

namespace Erm\Factory\Handler\Api\Risk;

use Erm\Handler\Api\Risk\RiskListHandler;
use Erm\Handler\Api\Risk\RiskResponseTypeHandler;
use Erm\Handler\Api\Rule\RuleListHandler;
use Erm\Handler\Api\RuleHandler;
use Erm\Handler\Api\Task\TaskListHandler;
use Erm\Service\TaskService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class RiskResponseTypeHandlerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param mixed[]|null       $options
     *
     * @return RiskResponseTypeHandler
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null):RiskResponseTypeHandler
    {
        return new RiskResponseTypeHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(TaskService::class)
        );
    }
}