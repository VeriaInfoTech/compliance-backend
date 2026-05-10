<?php

namespace Erm\Factory\Service;

use Erm\Repository\ComplianceTaskRepositoryInterface;
use Erm\Service\ComplianceTaskService;
use Erm\Service\TaskService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Core\Service\UtilityService;
use Pi\User\Service\AccountService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ComplianceTaskServiceFactory implements FactoryInterface
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ComplianceTaskService
    {
        return new ComplianceTaskService(
            $container->get(ComplianceTaskRepositoryInterface::class),
            $container->get(TaskService::class),
            $container->get(AccountService::class),
            $container->get(UtilityService::class),
        );
    }
}
