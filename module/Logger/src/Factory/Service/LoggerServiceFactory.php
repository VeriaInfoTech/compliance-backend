<?php

namespace Pi\Logger\Factory\Service;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Core\Service\UtilityService;
use Pi\Logger\Repository\LogRepositoryInterface;
use Pi\Logger\Service\LoggerService;
use Psr\Container\ContainerInterface;

class LoggerServiceFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LoggerService
    {
        $config = $container->get('config');
        $config = $config['logger'] ?? [];

        return new LoggerService(
            $container->get(LogRepositoryInterface::class),
            $container->get(UtilityService::class),
            $config
        );
    }
}
