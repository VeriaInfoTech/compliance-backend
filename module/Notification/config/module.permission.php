<?php

return [
    'admin' => [
        [
            'title'       => 'Admin notification list',
            'module'      => 'notification',
            'section'     => 'admin',
            'package'     => 'notification',
            'handler'     => 'list',
            'permissions' => 'notification-notification-list',
            'role'        => [
                'admin',
            ],
        ],
        [
            'title'       => 'Admin notification send',
            'module'      => 'notification',
            'section'     => 'admin',
            'package'     => 'notification',
            'handler'     => 'send',
            'permissions' => 'notification-notification-send',
            'role'        => [
                'admin',
            ],
        ],
    ],
    'api'   => [
        [
            'title'       => 'Notification count',
            'module'      => 'notification',
            'section'     => 'api',
            'package'     => 'notification',
            'handler'     => 'count',
            'permissions' => 'notification-count',
            'role'        => [
                'member',
                'admin',
            ],
        ],
        [
            'title'       => 'Notification list',
            'module'      => 'notification',
            'section'     => 'api',
            'package'     => 'notification',
            'handler'     => 'list',
            'permissions' => 'notification-list',
            'role'        => [
                'member',
                'admin',
            ],
        ],
        [
            'title'       => 'Notification send',
            'module'      => 'notification',
            'section'     => 'api',
            'package'     => 'notification',
            'handler'     => 'send',
            'permissions' => 'notification-send',
            'role'        => [
                'member',
                'admin',
            ],
        ],
    ],
];