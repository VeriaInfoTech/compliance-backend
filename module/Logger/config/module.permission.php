<?php


return [
    'admin' => [
        [
            'title'      => 'Admin logger user list',
            'module'     => 'logger',
            'section'    => 'admin',
            'package'    => 'system',
            'handler'    => 'list',
            'permission' => 'admin-logger-system-list',
            'role'       => [
                'admin',
            ],
        ],
        [
            'title'      => 'Admin logger user list',
            'module'     => 'logger',
            'section'    => 'admin',
            'package'    => 'user',
            'handler'    => 'list',
            'permission' => 'admin-logger-user-list',
            'role'       => [
                'admin',
            ],
        ],
        [
            'title'      => 'Admin logger history list',
            'module'     => 'logger',
            'section'    => 'admin',
            'package'    => 'history',
            'handler'    => 'list',
            'permission' => 'admin-logger-history-list',
            'role'       => [
                'admin',
            ],
        ],
        [
            'title'      => 'Admin logger manage repository',
            'module'     => 'logger',
            'section'    => 'admin',
            'package'    => 'manage',
            'handler'    => 'repository',
            'permission' => 'admin-logger-manage-repository',
            'role'       => [
                'admin',
            ],
        ],
    ],
];