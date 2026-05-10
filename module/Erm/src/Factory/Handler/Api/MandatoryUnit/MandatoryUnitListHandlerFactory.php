<?php

namespace Erm\Factory\Handler\Api\MandatoryUnit;

use Erm\Handler\Api\Domain\DomainTreeHandler;
use Erm\Handler\Api\MandatoryUnit\MandatoryUnitListHandler;
use Erm\Handler\Api\Rule\RuleListHandler;
use Erm\Handler\Api\RuleHandler;
use Erm\Handler\Api\Warranty\WarrantyListHandler;
use Erm\Model\Warranty;
use Erm\Service\TaskService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class MandatoryUnitListHandlerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return WarrantyListHandler
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null):MandatoryUnitListHandler
    {
        return new MandatoryUnitListHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(TaskService::class)
        );
    }
}