<?php

namespace Content\Factory\Repository;

use Content\Model\Item;
use Content\Model\Key;
use Content\Model\Meta;
use Content\Repository\ItemRepository;
use Interop\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Hydrator\ReflectionHydrator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;


class ItemRepositoryFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return ItemRepository
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ItemRepository
    {
        return new ItemRepository(
            $container->get(AdapterInterface::class),
            new ReflectionHydrator(),
            new Item('', 0,'', '', 0, 0, 0, 0, 0, 0, '', 0),
            new Meta(0, 0, 0,0, 0,0,0,0, '', 0,0,0,0, 0),
            new Key(0,0,0, 0, 0, 0, 0, 0, 0)
        );
    }
}
