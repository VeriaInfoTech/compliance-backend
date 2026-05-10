<?php

return [
    'api'   => [
        [
            'module'      => 'risk',
            'section'     => 'api',
            'package'     => 'adjustment',
            'handler'     => 'dashboard',
            'permissions' => 'risk-adjustment',
            'role'        => [
                'member',
                'admin',
            ],
        ],
        [
            'module'      => 'risk',
            'section'     => 'api',
            'package'     => 'adjustment',
            'handler'     => 'result',
            'permissions' => 'risk-adjustment',
            'role'        => [
                'member',
                'admin',
            ],
        ],
        [
            'module'      => 'risk',
            'section'     => 'api',
            'package'     => 'adjustment',
            'handler'     => 'import',
            'permissions' => 'risk-adjustment',
            'role'        => [
                'member',
                'admin',
            ],
        ],
        [
            'module'      => 'risk',
            'section'     => 'api',
            'package'     => 'adjustment',
            'handler'     => 'accept',
            'permissions' => 'risk-adjustment',
            'role'        => [
                'member',
                'admin',
            ],
        ],
    ],
];