<?php

namespace Erm\Factory\Handler\Api\Domain;

use Erm\Handler\Api\Domain\DomainTreeHandler;
use Erm\Handler\Api\Rule\RuleListHandler;
use Erm\Handler\Api\RuleHandler;
use Erm\Service\TaskService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class DomainTreeHandlerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param mixed[]|null       $options
     *
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null):DomainTreeHandler
    {
        $config = $container->get('config');
        return new DomainTreeHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(TaskService::class),
            $config['erm']
        );
    }
}