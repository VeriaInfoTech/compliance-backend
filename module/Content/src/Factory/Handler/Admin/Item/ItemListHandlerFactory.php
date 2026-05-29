<?php

namespace Content\Factory\Handler\Admin\Item;

use Content\Handler\Admin\Item\ItemListHandler;
use Content\Service\ItemService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ItemListHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ItemListHandler
    {
        return new ItemListHandler($container->get(ItemService::class));
    }
}
