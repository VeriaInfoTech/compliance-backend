<?php

namespace Content\Factory\Service;

use Content\Repository\ItemRepositoryInterface;
use Content\Service\ItemService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Pi\Core\Service\UtilityService;

class ItemServiceFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return ItemService
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ItemService
    {
        return new ItemService(
            $container->get(ItemRepositoryInterface::class),
            $container->get(UtilityService::class)
        );
    }
}
