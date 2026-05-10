<?php

namespace Pi\Logger\Factory\Middleware;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Core\Handler\ErrorHandler;
use Pi\Core\Service\UtilityService;
use Pi\Logger\Middleware\LoggerRequestResponseMiddleware;
use Pi\Logger\Service\LoggerService;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class LoggerRequestResponseMiddlewareFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): LoggerRequestResponseMiddleware
    {
        return new LoggerRequestResponseMiddleware(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(UtilityService::class),
            $container->get(ErrorHandler::class),
            $container->get(LoggerService::class)
        );
    }
}