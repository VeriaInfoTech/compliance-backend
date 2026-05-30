<?php

namespace Content\Factory\Service;

use Content\Repository\ItemRepositoryInterface;
use Content\Service\ItemBulkService;
use Content\Service\ItemService;
use Psr\Container\ContainerInterface;

class ItemBulkServiceFactory
{
    public function __invoke(ContainerInterface $container): ItemBulkService
    {
        $itemService = $container->get(ItemService::class);
        $itemRepository = $container->get(ItemRepositoryInterface::class);

        return new ItemBulkService($itemService, $itemRepository);
    }
}
