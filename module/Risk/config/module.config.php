<?php

namespace Risk;

use Laminas\Mvc\Middleware\PipeSpec;
use Laminas\Router\Http\Literal;
use Pi\User\Middleware\AuthenticationMiddleware;
use Pi\User\Middleware\AuthorizationMiddleware;
use Pi\Core\Middleware\SecurityMiddleware;

return [
    'service_manager' => [
        'aliases'   => [
            Repository\AdjustmentRepositoryInterface::class => Repository\AdjustmentRepository::class,
            Repository\FormRepositoryInterface::class       => Repository\FormRepository::class,
        ],
        'factories' => [
            Repository\AdjustmentRepository::class         => Factory\Repository\AdjustmentRepositoryFactory::class,
            Repository\FormRepository::class               => Factory\Repository\FormRepositoryFactory::class,
            Service\AdjustmentService::class               => Factory\Service\AdjustmentServiceFactory::class,
            Service\FormService::class                     => Factory\Service\FormServiceFactory::class,
            Service\SpreadsheetService::class              => Factory\Service\SpreadsheetServiceFactory::class,
            Middleware\ValidationMiddleware::class         => Factory\Middleware\ValidationMiddlewareFactory::class,
            Handler\Api\Adjustment\DashboardHandler::class => Factory\Handler\Api\Adjustment\DashboardHandlerFactory::class,
            Handler\Api\Adjustment\ResultHandler::class    => Factory\Handler\Api\Adjustment\ResultHandlerFactory::class,
            Handler\Api\Adjustment\ImportHandler::class    => Factory\Handler\Api\Adjustment\ImportHandlerFactory::class,
            Handler\Api\Adjustment\AcceptHandler::class    => Factory\Handler\Api\Adjustment\AcceptHandlerFactory::class,
            Handler\InstallerHandler::class                => Factory\Handler\InstallerHandlerFactory::class,
        ],
    ],
    'router'          => [
        'routes' => [
            // Api section
            'api_risk'   => [
                'type'         => Literal::class,
                'options'      => [
                    'route'    => '/risk',
                    'defaults' => [],
                ],
                'child_routes' => [
                    // Admin profile section
                    'adjustment' => [
                        'type'         => Literal::class,
                        'options'      => [
                            'route'    => '/adjustment',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'dashboard' => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/dashboard',
                                    'defaults' => [
                                        'module'     => 'risk',
                                        'section'    => 'api',
                                        'package'    => 'adjustment',
                                        'handler'    => 'dashboard',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            Handler\Api\Adjustment\DashboardHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'result'    => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/result',
                                    'defaults' => [
                                        'module'     => 'risk',
                                        'section'    => 'api',
                                        'package'    => 'adjustment',
                                        'handler'    => 'result',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            Handler\Api\Adjustment\ResultHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'import'    => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/import',
                                    'defaults' => [
                                        'module'     => 'risk',
                                        'section'    => 'api',
                                        'package'    => 'adjustment',
                                        'handler'    => 'import',
                                        'validator'  => 'attache',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            Middleware\ValidationMiddleware::class,
                                            Handler\Api\Adjustment\ImportHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'accept'    => [
                                'type'    => Literal::class,
                                'options' => [
                                    'route'    => '/accept',
                                    'defaults' => [
                                        'module'     => 'risk',
                                        'section'    => 'api',
                                        'package'    => 'adjustment',
                                        'handler'    => 'accept',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            Handler\Api\Adjustment\AcceptHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            // Admin section
            'admin_risk' => [
                'type'         => Literal::class,
                'options'      => [
                    'route'    => '/admin/risk',
                    'defaults' => [],
                ],
                'child_routes' => [
                    // Admin installer
                    'installer' => [
                        'type'    => Literal::class,
                        'options' => [
                            'route'    => '/installer',
                            'defaults' => [
                                'module'     => 'risk',
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