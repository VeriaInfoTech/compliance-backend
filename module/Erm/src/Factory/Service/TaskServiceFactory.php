<?php

namespace Erm\Factory\Service;

use Erm\Repository\TaskRepositoryInterface;
use Erm\Service\TaskService;
use Laminas\Cache\Service\StorageAdapterFactoryInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Logger\Service\LoggerService;
use Pi\Media\Service\MediaService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Pi\User\Service\AccountService;
use Pi\User\Service\HistoryService;
 use Pi\User\Service\RoleService;
use Pi\Core\Service\UtilityService;

class TaskServiceFactory implements FactoryInterface
{

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return TaskService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): TaskService
    {
        $config = $container->get('config');
        return new TaskService(
            $container->get(StorageAdapterFactoryInterface::class),
            $container->get(TaskRepositoryInterface::class),
            $container->get(RoleService::class),
            $container->get(AccountService::class),
            $container->get(LoggerService::class),
            $container->get(UtilityService::class),
            $container->get(MediaService::class),
            $config['erm'],
            $config['cache'],
        );
    }
}