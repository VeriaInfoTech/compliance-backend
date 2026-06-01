<?php

namespace Content\Factory\Handler\Api\Item;

use Content\Handler\Api\Item\ItemUpdateHandler;
use Content\Service\ItemService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ItemUpdateHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): ItemUpdateHandler
    {
        return new ItemUpdateHandler(
            $container->get(ItemService::class)
        );
    }
}
