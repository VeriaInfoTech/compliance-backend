<?php

namespace Risk\Factory\Service;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Risk\Repository\AdjustmentRepositoryInterface;
use Risk\Service\AdjustmentService;
use Risk\Service\FormService;
use Risk\Service\SpreadsheetService;

class AdjustmentServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return AdjustmentService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): AdjustmentService
    {
        return new AdjustmentService(
            $container->get(AdjustmentRepositoryInterface::class),
            $container->get(FormService::class),
            $container->get(SpreadsheetService::class)
        );
    }
}