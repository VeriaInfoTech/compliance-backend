<?php

namespace Pi\Notification\Factory\Repository;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Hydrator\ReflectionHydrator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Notification\Model\Storage;
use Pi\Notification\Repository\NotificationRepository;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;

class NotificationRepositoryFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return NotificationRepository
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): NotificationRepository
    {
        return new NotificationRepository(
            $container->get(AdapterInterface::class),
            new ReflectionHydrator(),
            new Storage(0, 0, 0, 0, 0, 0, 0, 0, '', '', 0),
        );
    }
}