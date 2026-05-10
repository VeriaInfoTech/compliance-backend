<?php

namespace Erm\Factory\Repository;

use Erm\Model\TaskList;
use Erm\Model\TaskProgress;
use Erm\Repository\ComplianceTaskRepository;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Hydrator\ReflectionHydrator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class ComplianceTaskRepositoryFactory implements FactoryInterface
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ComplianceTaskRepository
    {
        return new ComplianceTaskRepository(
            $container->get(AdapterInterface::class),
            new ReflectionHydrator(),
            new TaskList(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
            new TaskProgress(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
        );
    }
}
