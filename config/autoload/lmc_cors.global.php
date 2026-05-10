<?php

/**
 * This is the config file for ZfrCors. Just drop this file into your config/autoload folder (don't
 * forget to remove the .dist extension from the file), and configure it as you want
 */

return [
    'lmc_cors' => [
        /**
         * Set the list of allowed origins domain with protocol.
         */
         'allowed_origins' => ['*'],

        /**
         * Set the list of HTTP verbs.
         */
         'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],

        /**
         * Set the list of headers. This is returned in the preflight request to indicate
         * which HTTP headers can be used when making the actual request
         */
        'allowed_headers' => [
            'Authorization',
            'Content-Type',
            'token',
            'x-requested-with',
            'cache-control',
        ],
        'exposed_headers'   => ['Content-Type', 'Authorization'],
        'allowed_credentials' => true,

        /**
         * Set the max age of the preflight request in seconds. A non-zero max age means
         * that the preflight will be cached during this amount of time
         */
        // 'max_age' => 120,
    ],
];
