<?php

namespace Erm\Factory\Handler\Api\Insurance\Domain;

use Erm\Handler\Api\Insurance\Domain\InsuranceDomainTreeHandler;
use Erm\Handler\Api\Maturity\Domain\MaturityDomainTreeHandler;
use Erm\Service\TaskService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

class InsuranceDomainTreeHandlerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return InsuranceDomainTreeHandler
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null):InsuranceDomainTreeHandler
    {
        return new InsuranceDomainTreeHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(TaskService::class)
        );
    }
}