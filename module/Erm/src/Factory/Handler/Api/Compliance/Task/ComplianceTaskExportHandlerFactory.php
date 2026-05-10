<?php

namespace Erm\Factory\Handler\Api\Compliance\Task;

use Erm\Handler\Api\Compliance\Task\ComplianceTaskExportHandler;
use Erm\Service\TaskService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
 use Pi\User\Service\RoleService;

class ComplianceTaskExportHandlerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return ComplianceTaskExportHandler
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null):ComplianceTaskExportHandler
    {
        $config = $container->get('config');
        return new ComplianceTaskExportHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(TaskService::class),
            $container->get(RoleService::class),
            $config['erm']
        );
    }
}