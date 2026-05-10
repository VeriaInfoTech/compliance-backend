<?php

namespace Erm\Factory\Handler\Api\Risk;

use Erm\Handler\Api\Risk\RiskProgressHandler;
use Erm\Service\TaskService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
 use Pi\User\Service\RoleService;

class RiskProgressHandlerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param mixed[]|null       $options
     *
     * @return RiskProgressHandler
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null):RiskProgressHandler
    {
        return new RiskProgressHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(TaskService::class),
            $container->get(RoleService::class)
        );
    }
}