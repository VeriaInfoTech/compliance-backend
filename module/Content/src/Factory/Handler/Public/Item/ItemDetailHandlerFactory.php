<?php

namespace Content\Factory\Handler\Public\Item;

use Content\Handler\Public\Item\ItemDetailHandler;
use Content\Service\ItemService;
use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ItemDetailHandlerFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ItemDetailHandler
    {
        return new ItemDetailHandler($container->get(ItemService::class));
    }
}
