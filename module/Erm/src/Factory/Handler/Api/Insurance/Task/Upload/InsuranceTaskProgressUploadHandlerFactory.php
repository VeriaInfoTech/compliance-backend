<?php

namespace Erm\Factory\Handler\Api\Insurance\Task\Upload;

use Erm\Handler\Api\Insurance\Task\InsuranceTaskProgressHandler;
use Erm\Handler\Api\Insurance\Task\Upload\InsuranceTaskProgressUploadHandler;
use Erm\Service\TaskService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
 use Pi\User\Service\RoleService;

class InsuranceTaskProgressUploadHandlerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return InsuranceTaskProgressUploadHandler
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null):InsuranceTaskProgressUploadHandler
    {
        return new InsuranceTaskProgressUploadHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(TaskService::class),
            $container->get(RoleService::class)
        );
    }
}