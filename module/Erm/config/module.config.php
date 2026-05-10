<?php

namespace Erm;

use Laminas\Mvc\Middleware\PipeSpec;
use Laminas\Router\Http\Literal;
use Pi\Logger\Middleware\LoggerRequestResponseMiddleware;
use Pi\User\Middleware\AuthenticationMiddleware;
use Pi\User\Middleware\AuthorizationMiddleware;
use Pi\Core\Middleware\SecurityMiddleware;
use Pi\User\Middleware\RawDataValidationMiddleware;
return [
    'service_manager' => [
        'aliases' => [
            Repository\TaskRepositoryInterface::class => Repository\TaskRepository::class,
            Repository\ComplianceTaskRepositoryInterface::class => Repository\ComplianceTaskRepository::class,
        ],
        'factories' => [
            Repository\TaskRepository::class => Factory\Repository\TaskRepositoryFactory::class,
            Repository\ComplianceTaskRepository::class => Factory\Repository\ComplianceTaskRepositoryFactory::class,
            Service\TaskService::class => Factory\Service\TaskServiceFactory::class,
            Service\ComplianceTaskService::class => Factory\Service\ComplianceTaskServiceFactory::class,

            Handler\Api\AuditDashboardHandler::class => Factory\Handler\Api\AuditDashboardHandlerFactory::class,
            Handler\Api\AuditPerformanceHandler::class => Factory\Handler\Api\AuditPerformanceHandlerFactory::class,
            Handler\Api\AuditDetailHandler::class => Factory\Handler\Api\AuditDetailHandlerFactory::class,
            Handler\Api\AuditUpdateHandler::class => Factory\Handler\Api\AuditUpdateHandlerFactory::class,
            Handler\Api\AuditTreeHandler::class => Factory\Handler\Api\AuditTreeHandlerFactory::class,


            ///NEW ROUTER ON RAW DATA - JSON
            /// Rule
            Handler\Api\Rule\RuleImportHandler::class => Factory\Handler\Api\Rule\RuleImportHandlerFactory::class,
            Handler\Api\Rule\RuleExportHandler::class => Factory\Handler\Api\Rule\RuleExportHandlerFactory::class,
            Handler\Api\Rule\RuleListHandler::class => Factory\Handler\Api\Rule\RuleListHandlerFactory::class,
            Handler\Api\Rule\RuleLightListHandler::class => Factory\Handler\Api\Rule\RuleLightListHandlerFactory::class,
            Handler\Api\Rule\RuleEditHandler::class => Factory\Handler\Api\Rule\RuleEditHandlerFactory::class,
            Handler\Api\Rule\RuleAddHandler::class => Factory\Handler\Api\Rule\RuleAddHandlerFactory::class,
            Handler\Api\Rule\RuleDeleteHandler::class => Factory\Handler\Api\Rule\RuleDeleteHandlerFactory::class,
            Handler\Api\Rule\Category\RuleCategoryListHandler::class => Factory\Handler\Api\Rule\Category\RuleCategoryListHandlerFactory::class,
            Handler\Api\Rule\Type\RuleTypeListHandler::class => Factory\Handler\Api\Rule\Type\RuleTypeListHandlerFactory::class,
            Handler\Api\Rule\Type\RuleTypeAddHandler::class => Factory\Handler\Api\Rule\Type\RuleTypeAddHandlerFactory::class,
            Handler\Api\Rule\Type\RuleTypeEditHandler::class => Factory\Handler\Api\Rule\Type\RuleTypeEditHandlerFactory::class,
            Handler\Api\Rule\Type\RuleTypeDeleteHandler::class => Factory\Handler\Api\Rule\Type\RuleTypeDeleteHandlerFactory::class,
            Handler\Api\Rule\Author\RuleAuthorListHandler::class => Factory\Handler\Api\Rule\Author\RuleAuthorListHandlerFactory::class,
            Handler\Api\Rule\Author\RuleAuthorAddHandler::class => Factory\Handler\Api\Rule\Author\RuleAuthorAddHandlerFactory::class,
            Handler\Api\Rule\Author\RuleAuthorEditHandler::class => Factory\Handler\Api\Rule\Author\RuleAuthorEditHandlerFactory::class,
            Handler\Api\Rule\Author\RuleAuthorDeleteHandler::class => Factory\Handler\Api\Rule\Author\RuleAuthorDeleteHandlerFactory::class,


            /// Domain
            Handler\Api\Domain\DomainTreeHandler::class => Factory\Handler\Api\Domain\DomainTreeHandlerFactory::class,

            /// Warranty
            Handler\Api\Warranty\WarrantyListHandler::class => Factory\Handler\Api\Warranty\WarrantyListHandlerFactory::class,

            /// MandatoryUnit
            Handler\Api\MandatoryUnit\MandatoryUnitListHandler::class => Factory\Handler\Api\MandatoryUnit\MandatoryUnitListHandlerFactory::class,

            /// Answer
            Handler\Api\Answer\AnswerListHandler::class => Factory\Handler\Api\Answer\AnswerListHandlerFactory::class,

            /// Task
            Handler\Api\Task\TaskListHandler::class => Factory\Handler\Api\Task\TaskListHandlerFactory::class,
            Handler\Api\Task\TaskImportHandler::class => Factory\Handler\Api\Task\TaskImportHandlerFactory::class,
            Handler\Api\Task\TaskAddHandler::class => Factory\Handler\Api\Task\TaskAddHandlerFactory::class,
            Handler\Api\Task\TaskEditHandler::class => Factory\Handler\Api\Task\TaskEditHandlerFactory::class,
            Handler\Api\Task\TaskDeleteHandler::class => Factory\Handler\Api\Task\TaskDeleteHandlerFactory::class,
            Handler\Api\Task\TaskGetHandler::class => Factory\Handler\Api\Task\TaskGetHandlerFactory::class,

            /// Member
            Handler\Api\Member\MemberListHandler::class => Factory\Handler\Api\Member\MemberListHandlerFactory::class,
            Handler\Api\Member\MemberLightListHandler::class => Factory\Handler\Api\Member\MemberLightListHandlerFactory::class,
            Handler\Api\Member\MemberAddHandler::class => Factory\Handler\Api\Member\MemberAddHandlerFactory::class,
            Handler\Api\Member\MemberUpdateHandler::class => Factory\Handler\Api\Member\MemberUpdateHandlerFactory::class,
            Handler\Api\Member\MemberStatusHandler::class => Factory\Handler\Api\Member\MemberStatusHandlerFactory::class,
            Handler\Api\Member\MemberPasswordHandler::class => Factory\Handler\Api\Member\MemberPasswordHandlerFactory::class,
            Handler\Api\Member\MemberDeleteHandler::class => Factory\Handler\Api\Member\MemberDeleteHandlerFactory::class,
            Handler\Api\Member\MemberViewHandler::class => Factory\Handler\Api\Member\MemberViewHandlerFactory::class,
            ///TODO: add other handlers of member section

            /// Role
            Handler\Api\Role\RoleListHandler::class => Factory\Handler\Api\Role\RoleListHandlerFactory::class,

            /// Compliance
            Handler\Api\Compliance\ComplianceProgressHandler::class => Factory\Handler\Api\Compliance\ComplianceProgressHandlerFactory::class,
            Handler\Api\Compliance\Detail\ComplianceProgressDetailHandler::class => Factory\Handler\Api\Compliance\Detail\ComplianceProgressDetailHandlerFactory::class,
            Handler\Api\Compliance\CompliancePerformanceReportHandler::class => Factory\Handler\Api\Compliance\CompliancePerformanceReportHandlerFactory::class,
            Handler\Api\Compliance\ComplianceDashboardHandler::class => Factory\Handler\Api\Compliance\ComplianceDashboardHandlerFactory::class,
            Handler\Api\Compliance\Task\ComplianceTaskExportHandler::class => Factory\Handler\Api\Compliance\Task\ComplianceTaskExportHandlerFactory::class,
            Handler\Api\Compliance\ComplianceTaskListHandler::class => Factory\Handler\Api\Compliance\ComplianceTaskListHandlerFactory::class,

            /// Maturity
            Handler\Api\Maturity\Domain\MaturityDomainTreeHandler::class => Factory\Handler\Api\Maturity\Domain\MaturityDomainTreeHandlerFactory::class,
            Handler\Api\Maturity\Task\MaturityTaskListHandler::class => Factory\Handler\Api\Maturity\Task\MaturityTaskListHandlerFactory::class,
            Handler\Api\Maturity\Task\MaturityTaskGetHandler::class => Factory\Handler\Api\Maturity\Task\MaturityTaskGetHandlerFactory::class,
            Handler\Api\Maturity\Task\MaturityTaskProgressHandler::class => Factory\Handler\Api\Maturity\Task\MaturityTaskProgressHandlerFactory::class,
            Handler\Api\Maturity\Task\MaturityTaskExportHandler::class => Factory\Handler\Api\Maturity\Task\MaturityTaskExportHandlerFactory::class,
            Handler\Api\Maturity\MaturityDashboardHandler::class => Factory\Handler\Api\Maturity\MaturityDashboardHandlerFactory::class,
            Handler\Api\Maturity\MaturityPerformanceReportHandler::class => Factory\Handler\Api\Maturity\MaturityPerformanceReportHandlerFactory::class,

            /// Insurance
            Handler\Api\Insurance\Domain\InsuranceDomainTreeHandler::class => Factory\Handler\Api\Insurance\Domain\InsuranceDomainTreeHandlerFactory::class,
            Handler\Api\Insurance\Task\InsuranceTaskProgressHandler::class => Factory\Handler\Api\Insurance\Task\InsuranceTaskProgressHandlerFactory::class,
            Handler\Api\Insurance\Task\InsuranceTaskGetHandler::class => Factory\Handler\Api\Insurance\Task\InsuranceTaskGetHandlerFactory::class,
            Handler\Api\Insurance\Task\InsuranceTaskListHandler::class => Factory\Handler\Api\Insurance\Task\InsuranceTaskListHandlerFactory::class,
            Handler\Api\Insurance\Task\InsuranceTaskExportHandler::class => Factory\Handler\Api\Insurance\Task\InsuranceTaskExportHandlerFactory::class,
            Handler\Api\Insurance\Task\Upload\InsuranceTaskProgressUploadHandler::class => Factory\Handler\Api\Insurance\Task\Upload\InsuranceTaskProgressUploadHandlerFactory::class,
            Handler\Api\Insurance\InsuranceDashboardHandler::class => Factory\Handler\Api\Insurance\InsuranceDashboardHandlerFactory::class,
            Handler\Api\Insurance\InsurancePerformanceReportHandler::class => Factory\Handler\Api\Insurance\InsurancePerformanceReportHandlerFactory::class,

            /// Risk
            Handler\Api\Risk\RiskListHandler::class => Factory\Handler\Api\Risk\RiskListHandlerFactory::class,
            Handler\Api\Risk\Detail\RiskProgressDetailHandler::class => Factory\Handler\Api\Risk\Detail\RiskProgressDetailHandlerFactory::class,
            Handler\Api\Risk\RiskProgressHandler::class => Factory\Handler\Api\Risk\RiskProgressHandlerFactory::class,
            Handler\Api\Risk\RiskResponseTypeHandler::class => Factory\Handler\Api\Risk\RiskResponseTypeHandlerFactory::class,
            Handler\Api\Risk\RiskPerformanceReportHandler::class => Factory\Handler\Api\Risk\RiskPerformanceReportHandlerFactory::class,
            Handler\Api\Risk\RiskDashboardHandler::class => Factory\Handler\Api\Risk\RiskDashboardHandlerFactory::class,
            Handler\Api\Risk\Task\RiskTaskAddHandler::class => Factory\Handler\Api\Risk\Task\RiskTaskAddHandlerFactory::class,
            Handler\Api\Risk\Task\RiskTaskListHandler::class => Factory\Handler\Api\Risk\Task\RiskTaskListHandlerFactory::class,

            /// Audit
            Handler\Api\Audit\Task\AuditTaskAddHandler::class => Factory\Handler\Api\Audit\Task\AuditTaskAddHandlerFactory::class,
            Handler\Api\Audit\Task\AuditTaskListHandler::class => Factory\Handler\Api\Audit\Task\AuditTaskListHandlerFactory::class,
            Handler\Api\Audit\AuditDashboardHandler::class => Factory\Handler\Api\Audit\AuditDashboardHandlerFactory::class,
            Handler\Api\Audit\AuditPerformanceReportHandler::class => Factory\Handler\Api\Audit\AuditPerformanceReportHandlerFactory::class,

            // Admin section
            Handler\Admin\InstallerHandler::class => Factory\Handler\Admin\InstallerHandlerFactory::class,


            /// SSO
            Handler\Api\SSOLoginHandler::class => Factory\Handler\Api\SSOLoginHandlerFactory::class,


        ],
    ],
    'router' => [
        'routes' => [
            // Api section
            'api_erm' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/erm',
                    'defaults' => [],
                ],
                'child_routes' => [

                    ///NEW ROUTER ON RAW DATA - JSON
                    ///


                    'sso' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/sso',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'login' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/login',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'rule',
                                        'handler' => 'add',
                                        'permission' => 'api-erm-rule-add',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            Handler\Api\SSOLoginHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],
                    'rule' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/rule',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'import' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/import',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'rule',
                                        'handler' => 'add',
                                        'permission' => 'api-erm-rule-add',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Rule\RuleImportHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'export' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/export',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'rule',
                                        'handler' => 'list',
                                        'permission' => 'api-erm-rule-list',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Rule\RuleExportHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'list' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/list',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'rule',
                                        'handler' => 'list',
                                        'permission' => 'api-erm-rule-list',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Rule\RuleListHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'light-list' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/light-list',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'rule',
                                        'handler' => 'light-list',
                                        'permission' => 'api-erm-rule-light-list',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Rule\RuleLightListHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'add' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/add',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'rule',
                                        'handler' => 'add',
                                        'permission' => 'api-erm-rule-add',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Rule\RuleAddHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'edit' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/edit',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'rule',
                                        'handler' => 'edit',
                                        'permission' => 'api-erm-rule-edit',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Rule\RuleEditHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'delete' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/delete',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'rule',
                                        'handler' => 'delete',
                                        'permission' => 'api-erm-rule-delete',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Rule\RuleDeleteHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'author' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/author',
                                    'defaults' => [],
                                ],
                                'child_routes' => [
                                    'add' => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/add',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'meta',
                                                'handler' => 'manager',
                                                'permission' => 'api-erm-meta-manager',
                                                'controller' => PipeSpec::class,
                                                'middleware' => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Rule\Author\RuleAuthorAddHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                    'edit' => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/edit',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'meta',
                                                'handler' => 'manager',
                                                'permission' => 'api-erm-meta-manager',
                                                'controller' => PipeSpec::class,
                                                'middleware' => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Rule\Author\RuleAuthorEditHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                    'delete' => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/delete',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'meta',
                                                'handler' => 'manager',
                                                'permission' => 'api-erm-meta-manager',
                                                'controller' => PipeSpec::class,
                                                'middleware' => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Rule\Author\RuleAuthorDeleteHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                    'list' => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/list',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'rule',
                                                'handler' => 'list',
                                                'permission' => 'api-erm-rule-list',
                                                'controller' => PipeSpec::class,
                                                'middleware' => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Rule\Author\RuleAuthorListHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'type' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/type',
                                    'defaults' => [],
                                ],
                                'child_routes' => [
                                    'add' => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/add',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'meta',
                                                'handler' => 'manager',
                                                'permission' => 'api-erm-meta-manager',
                                                'controller' => PipeSpec::class,
                                                'middleware' => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Rule\Type\RuleTypeAddHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                    'edit' => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/edit',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'meta',
                                                'handler' => 'manager',
                                                'permission' => 'api-erm-meta-manager',
                                                'controller' => PipeSpec::class,
                                                'middleware' => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Rule\Type\RuleTypeEditHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                    'delete' => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/delete',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'meta',
                                                'handler' => 'manager',
                                                'permission' => 'api-erm-meta-manager',
                                                'controller' => PipeSpec::class,
                                                'middleware' => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Rule\Type\RuleTypeDeleteHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                    'list' => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/list',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'rule',
                                                'handler' => 'list',
                                                'permission' => 'api-erm-rule-list',
                                                'controller' => PipeSpec::class,
                                                'middleware' => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Rule\Type\RuleTypeListHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'category' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/category',
                                    'defaults' => [],
                                ],
                                'child_routes' => [
                                    'list' => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/list',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'rule',
                                                'handler' => 'list',
                                                'permission' => 'api-erm-rule-list',
                                                'controller' => PipeSpec::class,
                                                'middleware' => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Rule\Category\RuleCategoryListHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],

                    'task' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/task',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'get' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/get',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'task',
                                        'handler' => 'list',
                                        'permission' => 'api-erm-task-list',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Task\TaskGetHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'list' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/list',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'task',
                                        'handler' => 'list',
                                        'permission' => 'api-erm-task-list',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Task\TaskListHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'import' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/import',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'task',
                                        'handler' => 'add',
                                        'permission' => 'api-erm-task-add',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Task\TaskImportHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'add' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/add',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'task',
                                        'handler' => 'add',
                                        'permission' => 'api-erm-task-add',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Task\TaskAddHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'edit' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/edit',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'task',
                                        'handler' => 'edit',
                                        'permission' => 'api-erm-task-edit',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Task\TaskEditHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'delete' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/delete',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'task',
                                        'handler' => 'delete',
                                        'permission' => 'api-erm-task-delete',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Task\TaskDeleteHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],

                    'domain' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/domain',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'list' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/tree',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'domain',
                                        'handler' => 'tree',
                                        'permission' => 'api-erm-domain-tree',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Domain\DomainTreeHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],

                    'mandatory-unit' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/mandatory-unit',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'list' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/list',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'warranty',
                                        'handler' => 'list',
                                        'permission' => 'api-erm-domain-list',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\MandatoryUnit\MandatoryUnitListHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],

                    'answer' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/answer',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'list' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/list',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'warranty',
                                        'handler' => 'list',
                                        'permission' => 'api-erm-domain-list',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Answer\AnswerListHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],

                    'warranty' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/warranty',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'list' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/list',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'warranty',
                                        'handler' => 'list',
                                        'permission' => 'api-erm-domain-list',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Warranty\WarrantyListHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],

                    'member' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/member',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'list' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/list',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'member',
                                        'handler' => 'list',
                                        'permission' => 'api-erm-member-list',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Member\MemberListHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'light-list' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/light-list',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'member',
                                        'handler' => 'light-list',
                                        'permission' => 'api-erm-member-light-list',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Member\MemberLightListHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'add' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/add',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'member',
                                        'handler' => 'add',
                                        'permission' => 'api-erm-member-add',
                                        'validator' => 'add',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Member\MemberAddHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'view' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/view',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'member',
                                        'handler' => 'list',
                                        'permission' => 'api-erm-member-list',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Member\MemberViewHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'update' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/update',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'member',
                                        'handler' => 'add',
                                        'permission' => 'api-erm-member-add',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Member\MemberUpdateHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'status' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/status',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'member',
                                        'handler' => 'add',
                                        'permission' => 'api-erm-member-add',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Member\MemberStatusHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'delete' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/delete',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'member',
                                        'handler' => 'add',
                                        'permission' => 'api-erm-member-add',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Member\MemberDeleteHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'password' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/password',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'member',
                                        'handler' => 'add',
                                        'permission' => 'api-erm-member-add',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Member\MemberPasswordHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],

                    'role' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/role',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'list' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/list',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'role',
                                        'handler' => 'list',
                                        'permission' => 'api-erm-role-list',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Role\RoleListHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],

                    'compliance' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/compliance',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'list' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/list',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        // Must match task list permission page key (middleware builds api-{section}-{module}-{package}-{handler}).
                                        'package' => 'task',
                                        'handler' => 'list',
                                        'permission' => 'api-erm-task-list',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Compliance\ComplianceTaskListHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'progress' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/progress',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'compliance',
                                        'handler' => 'progress',
                                        'permission' => 'api-erm-compliance-progress',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Compliance\ComplianceProgressHandler::class
                                        ),
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'detail' => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/detail',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'compliance',
                                                'handler' => 'progress',
                                                'permission' => 'api-erm-compliance-progress',
                                                'validator' => 'global',
                                                'controller' => PipeSpec::class,
                                                'middleware' => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    RawDataValidationMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Compliance\Detail\ComplianceProgressDetailHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'task'   => [
                                'type'         => Literal::class,
                                'options'      => [
                                    'route'    => '/task',
                                    'defaults' => [],
                                ],
                                'child_routes' => [
                                    'export' => [
                                        'type'    => Literal::class,
                                        'options' => [
                                            'route'    => '/export',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'compliance',
                                                'handler' => 'task',
                                                'permission' => 'api-erm-compliance-task',
                                                'controller'  => PipeSpec::class,
                                                'middleware'  => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Compliance\Task\ComplianceTaskExportHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'performance' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/performance',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'compliance',
                                        'handler' => 'progress',
                                        'permission' => 'api-erm-compliance-progress',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Compliance\CompliancePerformanceReportHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'dashboard' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/dashboard',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'compliance',
                                        'handler' => 'dashboard',
                                        'permission' => 'api-erm-compliance-dashboard',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Compliance\ComplianceDashboardHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],

                    'maturity' => [
                        'type'         => Literal::class,
                        'options'      => [
                            'route'    => '/maturity',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'domain'   => [
                                'type'         => Literal::class,
                                'options'      => [
                                    'route'    => '/domain',
                                    'defaults' => [],
                                ],
                                'child_routes' => [
                                    'tree' => [
                                        'type'    => Literal::class,
                                        'options' => [
                                            'route'    => '/tree',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'maturity',
                                                'handler' => 'domain',
                                                'permission' => 'api-erm-maturity-domain',
                                                'controller'  => PipeSpec::class,
                                                'middleware'  => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Maturity\Domain\MaturityDomainTreeHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'task'   => [
                                'type'         => Literal::class,
                                'options'      => [
                                    'route'    => '/task',
                                    'defaults' => [],
                                ],
                                'child_routes' => [

                                    'export' => [
                                        'type'    => Literal::class,
                                        'options' => [
                                            'route'    => '/export',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'maturity',
                                                'handler' => 'task',
                                                'permission' => 'api-erm-maturity-task',
                                                'controller'  => PipeSpec::class,
                                                'middleware'  => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Maturity\Task\MaturityTaskExportHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                    'list' => [
                                        'type'    => Literal::class,
                                        'options' => [
                                            'route'    => '/list',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'maturity',
                                                'handler' => 'task',
                                                'permission' => 'api-erm-maturity-task',
                                                'controller'  => PipeSpec::class,
                                                'middleware'  => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Maturity\Task\MaturityTaskListHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                    'get' => [
                                        'type'    => Literal::class,
                                        'options' => [
                                            'route'    => '/get',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'maturity',
                                                'handler' => 'task',
                                                'permission' => 'api-erm-maturity-task',
                                                'controller'  => PipeSpec::class,
                                                'middleware'  => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Maturity\Task\MaturityTaskGetHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                    'progress' => [
                                        'type'    => Literal::class,
                                        'options' => [
                                            'route'    => '/progress',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'maturity',
                                                'handler' => 'task',
                                                'permission' => 'api-erm-maturity-task',
                                                'controller'  => PipeSpec::class,
                                                'middleware'  => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Maturity\Task\MaturityTaskProgressHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'dashboard' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/dashboard',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'insurance',
                                        'handler' => 'task',
                                        'permission' => 'api-erm-insurance-task',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Maturity\MaturityDashboardHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'performance' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/performance',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'insurance',
                                        'handler' => 'task',
                                        'permission' => 'api-erm-insurance-task',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Maturity\MaturityPerformanceReportHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],

                    'insurance' => [
                        'type'         => Literal::class,
                        'options'      => [
                            'route'    => '/insurance',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'domain'   => [
                                'type'         => Literal::class,
                                'options'      => [
                                    'route'    => '/domain',
                                    'defaults' => [],
                                ],
                                'child_routes' => [
                                    'tree' => [
                                        'type'    => Literal::class,
                                        'options' => [
                                            'route'    => '/tree',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'insurance',
                                                'handler' => 'domain',
                                                'permission' => 'api-erm-insurance-domain',
                                                'controller'  => PipeSpec::class,
                                                'middleware'  => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Insurance\Domain\InsuranceDomainTreeHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'progress'   => [
                                'type'         => Literal::class,
                                'options'      => [
                                    'route'    => '/progress',
                                    'defaults' => [],
                                ],
                                'child_routes' => [
                                    'tree' => [
                                        'type'    => Literal::class,
                                        'options' => [
                                            'route'    => '/detail',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'insurance',
                                                'handler' => 'domain',
                                                'permission' => 'api-erm-insurance-task',
                                                'controller'  => PipeSpec::class,
                                                'middleware'  => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Insurance\Task\InsuranceTaskGetHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'task'   => [
                                'type'         => Literal::class,
                                'options'      => [
                                    'route'    => '/task',
                                    'defaults' => [],
                                ],
                                'child_routes' => [
                                    'export' => [
                                        'type'    => Literal::class,
                                        'options' => [
                                            'route'    => '/export',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'insurance',
                                                'handler' => 'task',
                                                'permission' => 'api-erm-insurance-task',
                                                'controller'  => PipeSpec::class,
                                                'middleware'  => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Insurance\Task\InsuranceTaskExportHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                    'list' => [
                                        'type'    => Literal::class,
                                        'options' => [
                                            'route'    => '/list',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'insurance',
                                                'handler' => 'task',
                                                'permission' => 'api-erm-insurance-task',
                                                'controller'  => PipeSpec::class,
                                                'middleware'  => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Insurance\Task\InsuranceTaskListHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                    'get' => [
                                        'type'    => Literal::class,
                                        'options' => [
                                            'route'    => '/get',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'insurance',
                                                'handler' => 'task',
                                                'permission' => 'api-erm-insurance-task',
                                                'controller'  => PipeSpec::class,
                                                'middleware'  => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Insurance\Task\InsuranceTaskGetHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                    'progress' => [
                                        'type'    => Literal::class,
                                        'options' => [
                                            'route'    => '/progress',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'insurance',
                                                'handler' => 'task',
                                                'permission' => 'api-erm-insurance-task',
                                                'controller'  => PipeSpec::class,
                                                'middleware'  => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Insurance\Task\InsuranceTaskProgressHandler::class
                                                ),
                                            ],
                                        ],
                                        'may_terminate' => true,
                                        'child_routes' => [
                                            'upload' => [
                                                'type' => Literal::class,
                                                'options' => [
                                                    'route' => '/upload',
                                                    'defaults' => [
                                                        'module' => 'erm',
                                                        'section' => 'api',
                                                        'package' => 'insurance',
                                                        'handler' => 'task',
                                                        'permission' => 'api-erm-insurance-task',
                                                        'controller' => PipeSpec::class,
                                                        'middleware' => new PipeSpec(
                                                            SecurityMiddleware::class,
                                                            AuthenticationMiddleware::class,
                                                            AuthorizationMiddleware::class, 
                                                            LoggerRequestResponseMiddleware::class,
                                                            Handler\Api\Insurance\Task\Upload\InsuranceTaskProgressUploadHandler::class
                                                        ),
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'dashboard' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/dashboard',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'insurance',
                                        'handler' => 'task',
                                        'permission' => 'api-erm-insurance-task',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Insurance\InsuranceDashboardHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'performance' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/performance',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'insurance',
                                        'handler' => 'task',
                                        'permission' => 'api-erm-insurance-task',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Insurance\InsurancePerformanceReportHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],

                    'risk' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/risk',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'list' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/list',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'risk',
                                        'handler' => 'list',
                                        'permission' => 'api-erm-risk-list',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Risk\RiskListHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'progress' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/progress',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'risk',
                                        'handler' => 'progress',
                                        'permission' => 'api-erm-risk-progress',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Risk\RiskProgressHandler::class
                                        ),
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'detail' => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/detail',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'risk',
                                                'handler' => 'progress',
                                                'permission' => 'api-erm-risk-progress',
                                                'validator' => 'global',
                                                'controller' => PipeSpec::class,
                                                'middleware' => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    RawDataValidationMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Risk\Detail\RiskProgressDetailHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'task' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/task',
                                    'defaults' => [],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'add' => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/add',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'task',
                                                'handler' => 'add',
                                                'permission' => 'api-erm-task-add',
                                                'validator' => 'global',
                                                'controller' => PipeSpec::class,
                                                'middleware' => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    RawDataValidationMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Risk\Task\RiskTaskAddHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                    'list' => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/list',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'task',
                                                'handler' => 'list',
                                                'permission' => 'api-erm-task-list',
                                                'validator' => 'global',
                                                'controller' => PipeSpec::class,
                                                'middleware' => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    RawDataValidationMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Risk\Task\RiskTaskListHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'response/type' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/response/type',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'risk',
                                        'handler' => 'progress',
                                        'permission' => 'api-erm-risk-progress',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Risk\RiskResponseTypeHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'performance' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/performance',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'risk',
                                        'handler' => 'list',
                                        'permission' => 'api-erm-risk-list',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Risk\RiskPerformanceReportHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'dashboard' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/dashboard',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'risk',
                                        'handler' => 'dashboard',
                                        'permission' => 'api-erm-risk-dashboard',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Risk\RiskDashboardHandler::class
                                        ),
                                    ],
                                ],
                            ],

                        ],
                    ],

                    'audit' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/audit',
                            'defaults' => [],
                        ],
                        'child_routes' => [
                            'task' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/task',
                                    'defaults' => [],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'add' => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/add',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'task',
                                                'handler' => 'add',
                                                'permission' => 'api-erm-task-add',
                                                'validator' => 'global',
                                                'controller' => PipeSpec::class,
                                                'middleware' => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    RawDataValidationMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Audit\Task\AuditTaskAddHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                    'list' => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/list',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'task',
                                                'handler' => 'list',
                                                'permission' => 'api-erm-task-list',
                                                'validator' => 'global',
                                                'controller' => PipeSpec::class,
                                                'middleware' => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    RawDataValidationMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Audit\Task\AuditTaskListHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'progress' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/progress',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'audit',
                                        'handler' => 'progress',
                                        'permission' => 'api-erm-audit-progress',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Audit\AuditProgressHandler::class
                                        ),
                                    ],
                                ],
                                'may_terminate' => true,
                                'child_routes' => [
                                    'detail' => [
                                        'type' => Literal::class,
                                        'options' => [
                                            'route' => '/detail',
                                            'defaults' => [
                                                'module' => 'erm',
                                                'section' => 'api',
                                                'package' => 'compliance',
                                                'handler' => 'progress',
                                                'permission' => 'api-erm-compliance-progress',
                                                'validator' => 'global',
                                                'controller' => PipeSpec::class,
                                                'middleware' => new PipeSpec(
                                                    SecurityMiddleware::class,
                                                    RawDataValidationMiddleware::class,
                                                    AuthenticationMiddleware::class,
                                                    AuthorizationMiddleware::class,
                                                    LoggerRequestResponseMiddleware::class,
                                                    Handler\Api\Compliance\Detail\ComplianceProgressDetailHandler::class
                                                ),
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                            'performance' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/performance',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'compliance',
                                        'handler' => 'progress',
                                        'permission' => 'api-erm-compliance-progress',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Audit\AuditPerformanceReportHandler::class
                                        ),
                                    ],
                                ],
                            ],
                            'dashboard' => [
                                'type' => Literal::class,
                                'options' => [
                                    'route' => '/dashboard',
                                    'defaults' => [
                                        'module' => 'erm',
                                        'section' => 'api',
                                        'package' => 'compliance',
                                        'handler' => 'dashboard',
                                        'permission' => 'api-erm-compliance-dashboard',
                                        'validator' => 'global',
                                        'controller' => PipeSpec::class,
                                        'middleware' => new PipeSpec(
                                            SecurityMiddleware::class,
                                            RawDataValidationMiddleware::class,
                                            AuthenticationMiddleware::class,
                                            AuthorizationMiddleware::class,
                                            LoggerRequestResponseMiddleware::class,
                                            Handler\Api\Audit\AuditDashboardHandler::class
                                        ),
                                    ],
                                ],
                            ],
                        ],
                    ],

                    ///OLD ROUTER

                    'audit-dashboard' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/audit-dashboard',
                            'defaults' => [
                                'module' => 'erm',
                                'section' => 'api',
                                'package' => 'erm',
                                'handler' => 'audit-dashboard',
                                'controller' => PipeSpec::class,
                                'middleware' => new PipeSpec(
                                    SecurityMiddleware::class,
                                    AuthenticationMiddleware::class,
                                    LoggerRequestResponseMiddleware::class,
                                    Handler\Api\AuditDashboardHandler::class
                                ),
                            ],
                        ],
                    ],
                    'audit-performance' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/audit-performance',
                            'defaults' => [
                                'module' => 'erm',
                                'section' => 'api',
                                'package' => 'erm',
                                'handler' => 'audit-performance',
                                'controller' => PipeSpec::class,
                                'middleware' => new PipeSpec(
                                    SecurityMiddleware::class,
                                    AuthenticationMiddleware::class,
                                    LoggerRequestResponseMiddleware::class,
                                    Handler\Api\AuditPerformanceHandler::class
                                ),
                            ],
                        ],
                    ],
                    'audit-tree' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/audit-tree',
                            'defaults' => [
                                'module' => 'erm',
                                'section' => 'api',
                                'package' => 'erm',
                                'handler' => 'audit-tree',
                                'controller' => PipeSpec::class,
                                'middleware' => new PipeSpec(
                                    SecurityMiddleware::class,
                                    AuthenticationMiddleware::class,
                                    LoggerRequestResponseMiddleware::class,
                                    Handler\Api\AuditTreeHandler::class
                                ),
                            ],
                        ],
                    ],
                    'audit-detail' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/audit-detail',
                            'defaults' => [
                                'module' => 'erm',
                                'section' => 'api',
                                'package' => 'erm',
                                'handler' => 'audit-detail',
                                'controller' => PipeSpec::class,
                                'middleware' => new PipeSpec(
                                    SecurityMiddleware::class,
                                    AuthenticationMiddleware::class,
                                    LoggerRequestResponseMiddleware::class,
                                    Handler\Api\AuditDetailHandler::class
                                ),
                            ],
                        ],
                    ],
                    'audit-update' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/audit-update',
                            'defaults' => [
                                'module' => 'erm',
                                'section' => 'api',
                                'package' => 'erm',
                                'handler' => 'audit-update',
                                'controller' => PipeSpec::class,
                                'middleware' => new PipeSpec(
                                    SecurityMiddleware::class,
                                    AuthenticationMiddleware::class,
                                    LoggerRequestResponseMiddleware::class,
                                    Handler\Api\AuditUpdateHandler::class
                                ),
                            ],
                        ],
                    ],

                ],
            ],

            // Admin section
            'admin_erm' => [
                'type' => Literal::class,
                'options' => [
                    'route' => '/admin/erm',
                    'defaults' => [],
                ],
                'child_routes' => [

                    // Admin installer
                    'installer' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/installer',
                            'defaults' => [
                                'module' => 'erm',
                                'section' => 'admin',
                                'package' => 'installer',
                                'handler' => 'installer',
                                'controller' => PipeSpec::class,
                                'middleware' => new PipeSpec(
                                    SecurityMiddleware::class,
                                    AuthenticationMiddleware::class,
                                    LoggerRequestResponseMiddleware::class,
                                    Handler\Admin\InstallerHandler::class
                                ),
                            ],
                        ],
                    ],

                    'rule-light-list' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/rule/light-list',
                            'defaults' => [
                                'module' => 'erm',
                                'section' => 'admin',
                                'package' => 'rule',
                                'handler' => 'light-list',
                                'permission' => 'admin-erm-rule-light-list',
                                'controller' => PipeSpec::class,
                                'middleware' => new PipeSpec(
                                    SecurityMiddleware::class,
                                    AuthenticationMiddleware::class,
                                    AuthorizationMiddleware::class,
                                    LoggerRequestResponseMiddleware::class,
                                    Handler\Api\Rule\RuleLightListHandler::class
                                ),
                            ],
                        ],
                    ],

                    'member-light-list' => [
                        'type' => Literal::class,
                        'options' => [
                            'route' => '/member/light-list',
                            'defaults' => [
                                'module' => 'erm',
                                'section' => 'admin',
                                'package' => 'member',
                                'handler' => 'light-list',
                                'permission' => 'admin-erm-member-light-list',
                                'controller' => PipeSpec::class,
                                'middleware' => new PipeSpec(
                                    SecurityMiddleware::class,
                                    AuthenticationMiddleware::class,
                                    AuthorizationMiddleware::class,
                                    LoggerRequestResponseMiddleware::class,
                                    Handler\Api\Member\MemberLightListHandler::class
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