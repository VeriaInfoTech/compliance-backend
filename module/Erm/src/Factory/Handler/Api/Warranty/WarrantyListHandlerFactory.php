<?php

namespace Erm\Factory\Handler\Api\Warranty;

use Erm\Handler\Api\Domain\DomainTreeHandler;
use Erm\Handler\Api\Rule\RuleListHandler;
use Erm\Handler\Api\RuleHandler;
use Erm\Handler\Api\Warranty\WarrantyListHandler;
use Erm\Model\Warranty;
use Erm\Service\TaskService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class WarrantyListHandlerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param mixed[]|null       $options
     *
     * @return object
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null):WarrantyListHandler
    {
        return new WarrantyListHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(TaskService::class)
        );
    }
}