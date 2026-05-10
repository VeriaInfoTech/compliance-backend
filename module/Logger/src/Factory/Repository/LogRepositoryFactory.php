<?php

namespace Pi\Logger\Factory\Repository;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Hydrator\ReflectionHydrator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Logger\Model\History;
use Pi\Logger\Model\System;
use Pi\Logger\Model\User;
use Pi\Logger\Repository\LogRepository;
use Psr\Container\ContainerInterface;

class LogRepositoryFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LogRepository
    {
        return new LogRepository(
            $container->get(AdapterInterface::class),
            new ReflectionHydrator(),
            new System('', '', 0, '', 0, 0, '', 0, '', '', '', '', '', 0),
            new User(0, 0, 0, '', '', '', '', '', '', '', '', '', '', 0),
            new History(0, 0, '', '', 0, 0, 0, '', '', '', '', '', 0)
        );
    }
}