<?php

namespace Erm\Factory\Handler\Api\Compliance;

use Erm\Handler\Api\Compliance\ComplianceTaskListHandler;
use Erm\Service\ComplianceTaskService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Pi\User\Service\RoleService;

class ComplianceTaskListHandlerFactory implements FactoryInterface
{
    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ComplianceTaskListHandler
    {
        $config = $container->get('config');

        return new ComplianceTaskListHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(ComplianceTaskService::class),
            $container->get(RoleService::class),
            $config['erm']
        );
    }
}
