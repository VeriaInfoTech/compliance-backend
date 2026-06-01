<?php

namespace Content\Factory\Handler\Admin\Item;

use Content\Handler\Admin\Item\ItemUpdateHandler;
use Content\Service\ItemBulkService;
use Content\Service\ItemService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ItemUpdateHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ItemUpdateHandler
    {
        return new ItemUpdateHandler(
            $container->get(ItemService::class),
            $container->get(ItemBulkService::class)
        );
    }
}
