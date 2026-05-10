<?php

namespace Erm\Factory\Handler\Api;

use Erm\Handler\Api\SSOLoginHandler;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Pi\User\Handler\Api\Authentication\Oauth\Oauth2Handler;
use Pi\User\Handler\Api\Authentication\Oauth\SettingHandler;

class SSOLoginHandlerFactory implements FactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     *
     * @return SSOLoginHandler
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null): SSOLoginHandler
    {
        $config = $container->get('config');
        return new SSOLoginHandler(
            $container->get(ResponseFactoryInterface::class),
            $container->get(StreamFactoryInterface::class),
            $container->get(Oauth2Handler::class),
            $config['account']['oauth']['oauth2']
        );
    }
}