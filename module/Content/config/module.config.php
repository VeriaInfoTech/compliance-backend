<?php

namespace Content;

use Laminas\Mvc\Middleware\PipeSpec;
use Laminas\Router\Http\Literal;
use Pi\Core\Middleware\RequestPreparationMiddleware;
use Pi\Core\Middleware\SecurityMiddleware;
use Pi\User\Middleware\AuthenticationMiddleware;
use Pi\User\Middleware\AuthorizationMiddleware;

return [
    'service_manager' => [
        'aliases' => [
            Repository\ItemRepositoryInterface::class => Repository\ItemRepository::class,
        ],
        'factories' => [
            Repository\ItemRepository::class             => Factory\Repository\ItemRepositoryFactory::class,
            Service\ItemService::class                   => Factory\Service\ItemServiceFactory::class,
            Middleware\ValidationMiddleware::class       => Factory\Middleware\ValidationMiddlewareFactory::class,
            Validator\SlugValidator::class               => Factory\Validator\SlugValidatorFactory::class,
            Validator\TypeValidator::class               => Factory\Validator\TypeValidatorFactory::class,
            Handler\Api\Item\ItemListHandler::class      => Factory\Handler\Api\Item\ItemListHandlerFactory::class,
            Handler\Api\Item\ItemDetailHandler::class    => Factory\Handler\Api\Item\ItemDetailHandlerFactory::class,
            Handler\InstallerHandler::class              => Factory\Handler\InstallerHandlerFactory::class,

            // Item
            Handler\Admin\Item\ItemListHandler::class    => Factory\Handler\Admin\Item\ItemListHandlerFactory::class,
            Handler\Admin\Item\ItemDetailHandler::class  => Factory\Handler\Admin\Item\ItemDetailHandlerFactory::class,

            ///Public Section
            // Item
            Handler\Public\Item\ItemListHandler::class   => Factory\Handler\Public\Item\ItemListHandlerFactory::class,
            Handler\Public\Item\ItemDetailHandler::class => Factory\Handler\Public\Item\ItemDetailHandlerFactory::class,

        ],
    ],

    'router' => [
        'routes' => [
            // Public section
            'public_content' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/public/content',
                    'defaults' => [],
                ],
                'child_routes' => [
                    'item' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/item',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'get' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/get',
                                    'defaults' => [
                                        'module' => 'content',
                                        'section' => 'public',
                                        'package' => 'item',
                                        'handler' => 'get',
                                        'permission' => 'public-content-item-get',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            Handler\Public\Item\ItemDetailHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'list' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/list',
                                    'defaults' => [
                                        'module' => 'content',
                                        'section' => 'public',
                                        'package' => 'item',
                                        'handler' => 'list',
                                        'permission' => 'public-content-item-list',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            RequestPreparationMiddleware::class,
                                            Handler\Public\Item\ItemListHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ]
                    ],
                ],
            ],
            // Api section
            'api_content' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/content',
                    'defaults' => [],
                ],
                'child_routes' => [
                    'list' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/list',
                            'defaults' => [
                                'module' => 'content',
                                'section' => 'api',
                                'package' => 'item',
                                'validator' => 'list',
                                'handler' => 'list',
                                'permissions' => 'api-item-list',
                                'controller' => PipeSpec::class,
                                'middleware' => new PipeSpec(
                                    SecurityMiddleware::class,
                                    Middleware\ValidationMiddleware::class,
                                    AuthenticationMiddleware::class,
                                    AuthorizationMiddleware::class,
                                    Handler\Api\Item\ItemListHandler::class
                                ),
                            ],
                        ],
                    ],
                    'detail' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/detail',
                            'defaults' => [
                                'module' => 'content',
                                'section' => 'api',
                                'package' => 'item',
                                'validator' => 'detail',
                                'handler' => 'detail',
                                'permission' => 'api-content-detail',
                                'controller' => PipeSpec::class,
                                'middleware' => new PipeSpec(
                                    SecurityMiddleware::class,
                                    Middleware\ValidationMiddleware::class,
                                    Handler\Api\Item\ItemDetailHandler::class
                                ),
                            ],
                        ],
                    ],
                    'item' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/item',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'list' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/list',
                                    'defaults' => [
                                        'module' => 'content',
                                        'section' => 'api',
                                        'package' => 'item',
                                        'validator' => 'list',
                                        'handler' => 'list',
                                        'permission' => 'api-content-item-list',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            Middleware\ValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            Handler\Api\Item\ItemListHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'detail' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/detail',
                                    'defaults' => [
                                        'module' => 'content',
                                        'section' => 'api',
                                        'package' => 'item',
                                        'validator' => 'detail',
                                        'handler' => 'detail',
                                        'permission' => 'api-content-item-detail',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            Middleware\ValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            Handler\Api\Item\ItemDetailHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // Admin section
            'admin_content' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/admin/content',
                    'defaults' => [],
                ],
                'child_routes' => [
                    'item' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/item',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'get' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/get',
                                    'defaults' => [
                                        'module' => 'content',
                                        'section' => 'admin',
                                        'package' => 'item',
                                        'handler' => 'get',
                                        'permission' => 'admin-content-item-get',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            Handler\Admin\Item\ItemDetailHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'list' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/list',
                                    'defaults' => [
                                        'module' => 'content',
                                        'section' => 'admin',
                                        'package' => 'item',
                                        'handler' => 'list',
                                        'permission' => 'admin-content-item-list',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            Handler\Admin\Item\ItemListHandler::class
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
];
