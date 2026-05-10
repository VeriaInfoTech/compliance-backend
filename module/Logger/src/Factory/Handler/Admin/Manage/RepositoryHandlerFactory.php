<?php

namespace Pi\Logger\Factory\Handler\Admin\Manage;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Pi\Logger\Handler\Admin\Manage\RepositoryHandler;
use Pi\Logger\Service\LoggerService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class RepositoryHandlerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param array|null         $options
     *
     * @return RepositoryHandler
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): RepositoryHandler
    {
        return new RepositoryHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(LoggerService::class)
        );
    }
}