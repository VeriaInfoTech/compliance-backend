<?php

namespace Content\Factory\Handler\Admin\Item;

use Content\Handler\Admin\Item\ItemAddHandler;
use Content\Service\ItemService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ItemAddHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ItemAddHandler
    {
        return new ItemAddHandler(
            $container->get(ItemService::class)
        );
    }
}
