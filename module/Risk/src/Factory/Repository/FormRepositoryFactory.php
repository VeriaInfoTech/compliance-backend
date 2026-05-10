<?php

namespace Risk\Factory\Repository;

use Interop\Container\ContainerInterface;
use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Hydrator\ReflectionHydrator;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Risk\Model\Form\Data;
use Risk\Model\Form\Element;
use Risk\Model\Form\Inventory;
use Risk\Model\Form\Link;
use Risk\Model\Form\Record;
use Risk\Repository\FormRepository;

class FormRepositoryFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return FormRepository
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): FormRepository
    {
        return new FormRepository(
            $container->get(AdapterInterface::class),
            new ReflectionHydrator(),
            new Inventory('', ''),
            new Element('', '', '', '', '', 1, 1),
            new Link(0, 0),
            new Record(0, 0, 0),
            new Data('', '', 0, 0, 0, 0, 0)
        );
    }
}