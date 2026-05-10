<?php

declare(strict_types=1);

namespace Pi\User\Factory\Middleware;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Core\Handler\ErrorHandler;
use Pi\Core\Service\CacheService;
use Pi\Core\Service\ConfigService;
use Pi\Core\Service\UtilityService;
use Pi\User\Middleware\RawDataValidationMiddleware;
use Pi\User\Service\AccountService;
use Psr\Container\ContainerInterface;

class RawDataValidationMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): RawDataValidationMiddleware
    {
        return new RawDataValidationMiddleware(
            $container->get(\Psr\Http\Message\ResponseFactoryInterface::class),
            $container->get(\Psr\Http\Message\StreamFactoryInterface::class),
            $container->get(AccountService::class),
            $container->get(UtilityService::class),
            $container->get(CacheService::class),
            $container->get(ConfigService::class),
            $container->get(ErrorHandler::class)
        );
    }
}
