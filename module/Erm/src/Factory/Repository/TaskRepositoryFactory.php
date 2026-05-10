<?php

namespace Erm\Factory\Repository;

use Erm\Model\ErmMeta;
use Erm\Model\MandatoryUnit;
use Erm\Model\MandatoryUnitMember;
use Erm\Model\TaskAudit;
use Erm\Model\TaskList;
use Erm\Model\TaskProgress;
use Erm\Model\TaskRisk;
use Erm\Model\TaskSection;
use Erm\Model\Rule;
use Erm\Model\Warranty;
use Erm\Repository\TaskRepository;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Hydrator\ReflectionHydrator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class TaskRepositoryFactory implements FactoryInterface
{

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return TaskRepository
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): TaskRepository
    {
        return new TaskRepository(
            $container->get(AdapterInterface::class),
            new ReflectionHydrator(),
            new TaskList(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,0,0, 0, 0),
            new TaskSection(0, 0, 0, 0,0, 0, 0, 0, 0, 0),
            new TaskProgress(0, 0,0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
            new TaskRisk(0, 0,0,0,0,0,0,0,0,0,0,0,0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,0, 0, 0, 0),
            new TaskAudit(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
            new Rule(0, 0,0,0,0,0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
            new Warranty(0, 0,0),
            new MandatoryUnit(0, 0,0,0),
            new MandatoryUnitMember(0, 0,0,0,0),
            new ErmMeta(0, 0,0,0,0,0,0,0,0,0,0,0),
        );
    }
}