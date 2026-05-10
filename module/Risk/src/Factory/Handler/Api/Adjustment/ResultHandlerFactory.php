<?php

namespace Risk\Factory\Handler\Api\Adjustment;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Risk\Handler\Api\Adjustment\ResultHandler;
use Risk\Service\AdjustmentService;
use Risk\Service\FormService;
use Risk\Service\SpreadsheetService;

class ResultHandlerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return ResultHandler
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ResultHandler
    {
        return new ResultHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(AdjustmentService::class),
            $container->get(FormService::class),
            $container->get(SpreadsheetService::class)
        );
    }
}