<?php

namespace Pi\Logger;

use Laminas\Mvc\Middleware\PipeSpec;
use Laminas\Router\Http\Literal;
use Pi\Core\Middleware\SecurityMiddleware;
use Pi\User\Middleware\AuthenticationMiddleware;
use Pi\User\Middleware\AuthorizationMiddleware;

return [
    'service_manager' => [
        'aliases'   => [
            Repository\LogRepositoryInterface::class => Repository\LogRepository::class,
        ],
        'factories' => [
            Repository\LogRepository::class                        => Factory\Repository\LogRepositoryFactory::class,
            Service\LoggerService::class                           => Factory\Service\LoggerServiceFactory::class,
            Middleware\LoggerRequestResponseMiddleware::class      => Factory\Middleware\LoggerRequestResponseMiddlewareFactory::class,
            Handler\InstallerHandler::class                        => Factory\Handler\InstallerHandlerFactory::class,
            Handler\Admin\History\ListHandler::class               => Factory\Handler\Admin\History\ListHandlerFactory::class,
            Handler\Admin\Manage\RepositoryHandler::class          => Factory\Handler\Admin\Manage\RepositoryHandlerFactory::class,
            Handler\Admin\System\ListHandler::class                => Factory\Handler\Admin\System\ListHandlerFactory::class,
            Handler\Admin\User\ListHandler::class                  => Factory\Handler\Admin\User\ListHandlerFactory::class,
        ],
    ],

    'router' => [
        'routes' => [
            'admin_content' => [
                'type'         => Literal::class,
                'options'      => [
                    'route'    => '/admin/logger',
                    'defaults' => [],
                ],
                'child_routes' => [
                    'installer' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/installer',
                            'defaults' => [
                                'module'     => 'logger',
                                'section'    => 'admin',
                                'package'    => 'installer',
                                'handler'    => 'installer',
                                'controller' => PipeSpec::class,
                                'middleware' => new PipeSpec(
                                    SecurityMiddleware::class,
                                    AuthenticationMiddleware::class,
                                    Handler\InstallerHandler::class
                                ),
                            ],
                        ],
                    ],
                    'inventory' => [
                        'type'         => Literal::class,
                        'options'      => [
                            'route'    => '/inventory',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'read' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/read',
                                    'defaults' => [
                                        'title'      => 'Admin logger inventory read',
                                        'module'     => 'logger',
                                        'section'    => 'admin',
                                        'package'    => 'inventory',
                                        'handler'    => 'read',
                                        'permission' => 'admin-logger-inventory-read',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            Handler\Admin\Manage\RepositoryHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'user'      => [
                        'type'         => Literal::class,
                        'options'      => [
                            'route'    => '/user',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'read' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/read',
                                    'defaults' => [
                                        'title'      => 'Admin logger user read',
                                        'module'     => 'logger',
                                        'section'    => 'admin',
                                        'package'    => 'user',
                                        'handler'    => 'read',
                                        'permission' => 'admin-logger-user-read',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            Handler\Admin\User\ListHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],

    'view_manager' => [
        'strategies' => [
            'ViewJsonStrategy',
        ],
    ],
];
