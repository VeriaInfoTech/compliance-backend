<?php

namespace Content\Factory\Validator;

use Content\Validator\SlugValidator;
use Interop\Container\Containerinterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class SlugValidatorFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string             $requestedName
     * @param null|array         $options
     *
     * @return SlugValidator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): SlugValidator
    {
        return new SlugValidator();
    }
}