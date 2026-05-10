<?php

/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

// Fallback for IntlDateFormatter when ext-intl is not loaded (e.g. CLI sync scripts)
if (!class_exists('IntlDateFormatter', false)) {
    class IntlDateFormatter {
        public const SHORT = 3;
        public const NONE = 0;
        public const TRADITIONAL = 1;
    }
}

$basePath = realpath(__DIR__ . '/../..') ?: '/app';
$baseUrl  = getenv('BASE_URL') ?: 'http://localhost:8080';

return [
    // IP utility config (consumed by Core services)
    'ip'           => [
        'local_ranges'    => ['127.', '::1'],
        'internal_ranges' => ['10.', '172.', '192.168.', 'fc00:', 'fd00:'],
    ],
    'global'       => [
        'sitename' => 'Shahr',
        'baseurl'  => $baseUrl,
    ],
    'db'           => [
        'driver'         => 'Pdo',
        'dsn'            => sprintf(
            'mysql:dbname=%s;host=%s;port=%s;charset=utf8',
            (getenv('MYSQL_DB_NAME') ?: 'shahr_db'),
            (getenv('MYSQL_DB_HOST') ?: 'localhost'),
            (getenv('MYSQL_PORT') ?: '3306'),
        ),
        'username'       => getenv('MYSQL_DB_USER') ?: 'magietoco_user',
        'password'       => getenv('MYSQL_DB_PASSWORD') ?: '1!qQ2@wW3#eE4$rR',
        'driver_options' => [
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_general_ci',
            PDO::ATTR_PERSISTENT         => false,
        ],
        'options'        => [],
    ],

    'cache'        => (PHP_SAPI === 'cli' || !extension_loaded('redis'))
        ? [
            'storage' => 'Memory',
            'options' => [
                'namespace' => 'local_laminas_cli_shahr',
                'ttl'       => 1209600,
            ],
            'plugins' => [
                ['name' => 'serializer'],
            ],
        ]
        : [
            'storage' => 'redis',
            'options' => [
                'namespace' => 'local_laminas_cli_shahrUser\\Service',
                'ttl'       => 1209600,
                'server'    => [
                    'host' => getenv('REDIS_HOST') ?: 'localhost',
                    'port' => getenv('REDIS_PORT') ?: '6379',
                ],
            ],
            'plugins' => [
                ['name' => 'serializer'],
            ],
        ],
    'roles' => [
        'default_roles' => [
            [
                'name' => 'member',
                'section' => 'api',
            ]
        ],
    ],
    'jwt' => [
        'secret' => '30d513d5af80e7c8cd3a6665a571fcdc0abd2d8b824f62ec1caa49dc334ba578',
        'exp_access' => 21600, // (1209600) 14 days, for development, for production set new value
        'exp_refresh' => 7776000, // 90 days
        'public_key'          => $basePath . '/data/keys/public_key.pem',
        'private_key'         => $basePath . '/data/keys/private_key.pem',
        'internal_public_key' => $basePath . '/data/keys/internal_public_key.pem',
        'internal_private_key'=> $basePath . '/data/keys/internal_private_key.pem',
        'iss'                 => '',
        'check_aud'           => 0,
        'check_ip'            => 0,
        'use_origin'          => 0,
        'additional'          => ['company_id', 'company_title', 'identity', 'email', 'name', 'first_name', 'last_name', 'avatar', 'roles'],
    ],
    'account' => [
        'otp_sms' => [
            'dafi' => 'کد تایید: %s
        دافی',
            'club' => 'Code: %s
        Seylaneh',
            'luxirana' => 'کد تایید: %s
        لوکس ایرانا',
            'affiliate' => 'کد تایید: %s
        لوکس ایرانا',
        ],
        'otp_email' => [
            'subject' => 'ورود با کد یک بار مصرف',
            'body' => 'کد یک بار مصرف شما برای ورود <strong>%s</strong> است و این کد به مدت ۳ دقیقه معتبر خواهد بود.',
        ],
        'multi_factor' => [
            'status'         => 0,
            'default_method' => 'sms',
            'allowed_method' => ['sms', 'email', 'app'],
            'sms'            => ['message' => 'Your verification code is: %s'],
            'email'          => [
                'subject' => 'Verify Your Code - Secure Login',
                'body'    => 'Your verification code is <strong>%s</strong>. It is valid for only 2 minutes.',
            ],
        ],
        'oauth' => [
            'oauth_login' => 1,
            'oauth_register' => 1,
            'microsoft' => [
                'status' => 1,
                'microsoft_callback' => 'XXX',
                'microsoft_client_id' => 'XXX',
                'microsoft_client_secret' => 'XXX',
                'microsoft_tenant_id' => 'XXX',
            ],
            'google' => [
                'status' => 1,
                'google_callback' => 'XXX',
                'google_client_id' => 'XXX',
                'google_client_secret' => 'XXX',
            ],
            'oauth2' => [
                'base_url' => 'https://sso.shahr-bank.ir/',
//                'authorize_url' => 'https://sso.shahr-bank.ir/connect/authorize?client_Id=%s&response_Type=%s&scope=%s&redirect_Uri=%s&state=%s&nonce=%s&response_Mode=%s',
                'authorize_url' => 'https://theirm.ir/test/redirect/',
//                'token_url' => 'https://sso.shahr-bank.ir/connect/token',
                'token_url' => 'https://theirm.ir/test/token/',
//                'user_info_url' => 'https://sso.shahr-bank.ir/connect/userinfo',
                'user_info_url' => 'https://theirm.ir/test/user-info/',
                'client_id' => 'ecc2a7c9-a9ab-43a9-b57f-d4f28e00d350',
                'client_secret' => '72e23c58-5fd4-8c9a-ece5-9ea27e335622',
                'response_type' => 'code',
                'scope' => 'openid profile',
                'redirect_uri' => 'https://compliance.shahr-bank.ir/api/erm/sso/login',
                'state' => 'state',
                'grant_type' => 'authorization_code',
                'nonce' => 'nonce',
                'response_mode' => 'form_post',
//                'client_login_url' => 'https://compliance.shahr-bank.ir/login?access_login=%s',
                'client_login_url' => 'http://localhost:89/login?access_login=%s',
            ]
        ],
        'register' => [
            'status' => 1,
        ],
        'login' => [
            'permission'           => 1,
            'get_company'          => 0,
            'session_policy'       => 'multi',
            'check_signature'      => 0,
            'permission_package'   => 1,
            'permission_role'      => ['api', 'admin'],
            'permission_blacklist' => [],
        ],
    ],
    'captcha' => [
        'recaptcha' => [
            'public' => '',
            'secret' => '',
        ],
    ],
    'export' => [
        'format'    => 'csv',
        'file_path' => $basePath . '/data/export/',
    ],
    'notification' => [
        'defaults' => [
            'mail' => 'phpMailer',
            'sms'  => 'payamakYab',
            'push' => 'fcm',
        ],
        'email' => [
            'laminas' => [
                'encoding' => 'utf-8',
                'from' => [
                    'email' => '',
                    'name' => '',
                ],
            ],
            'phpmailer' => [
                // use 587 if you have set `SMTPSecure => ENCRYPTION_STARTTLS`
                // use 465 if you have set `SMTPSecure => ENCRYPTION_SMTPS`
                'smtp' => [
                    'host' => '',
                    'username' => '',
                    'password' => '',
                    'port' => 587,
                    'SMTPSecure' => 'ENCRYPTION_STARTTLS',
                ],
                'from' => [
                    'email' => '',
                    'name' => '',
                ],
                'encoding' => 'utf-8',
            ],
        ],
        'sms' => [
            'nexmo' => [],
            'payamakYab' => [
                'username' => '',
                'password' => '',
                'number' => '',
                'url' => '',
                'dafi' => [
                    'username' => '',
                    'password' => '',
                    'number' => '',
                    'url' => '',
                ],
                'luxirana' => [
                    'username' => '',
                    'password' => '',
                    'number' => '',
                    'url' => '',
                ],
                'club' => [
                    'username' => '',
                    'password' => '',
                    'number' => '',
                    'url' => '',
                ],
                'affiliate' => [
                    'username' => '',
                    'password' => '',
                    'number' => '',
                    'url' => '',
                ],
                'risk' => [
                    'username' => '',
                    'password' => '',
                    'number' => '',
                    'url' => '',
                ],
            ],
        ],
        'push' => [
            'customer' => [
                'fcm' => [
                    'url' => 'https://fcm.googleapis.com/fcm/send',
                    'server_key' => '',
                ],
            ],
            'owner' => [
                'fcm' => [
                    'url' => 'https://fcm.googleapis.com/fcm/send',
                    'server_key' => '',
                ],
            ],
            'seylaneh' => [
                'fcm' => [
                    'url' => 'https://fcm.googleapis.com/fcm/send',
                    'server_key' => '',
                ],
            ],
            'risk' => [
                'fcm' => [
                    'url' => '',
                    'server_key' => '',
                ],
            ],
            'fcm' => [
                'setting' => [
                    'limitation' => ['status' => true, 'length' => 150],
                    'xss'        => ['status' => true],
                ],
            ],
            'apns' => [
                'key_id'             => '',
                'is_production'      => false,
                'team_id'            => '',
                'app_bundle_id'      => '',
                'private_key_path'   => '',
                'private_key_secret' => null,
                'setting'            => [
                    'limitation' => ['status' => true, 'length' => 150],
                    'xss'        => ['status' => true],
                ],
            ],
        ],
        'setting' => [
            'limitation' => [
                'status' => true,
                'length' => 150
            ],
            'xss' => [
                'status' => true,
            ]
        ]
    ],
    'client' => [
        'api' => [
            'status' => '1',
            'last_version' => '1',
            'authorized_versions' => ['1'],
            'all_versions' => ['1'],
            'message' => 'New version of api is available, please update your engin.',
            'url' => 'https://',
            'last_update' => 1677685045,
            'button_title' => 'Update',
            'title' => 'Attention',

        ],
        'application' => [
            'android' => [
                'status' => '1',
                'last_version' => '2',
                'authorized_versions' => ['2', '1'],
                'all_versions' => ['1', '4'],
                'message' => 'New version of application is available, please update your version.',
                'url' => 'https://',
                'last_update' => 1677685045,
                'is_force' => 0,
                'button_title' => 'Update',
                'title' => 'Attention',
            ],
            'ios' => [
                'status' => '1',
                'last_version' => '1',
                'authorized_versions' => ['2', '1'],
                'all_versions' => ['1'],
                'message' => 'New version of application is available, please update your version.',
                'url' => 'https://',
                'last_update' => 1677685045,
                'is_force' => 0,
                'button_title' => 'Update',
                'title' => 'Attention',
            ],
        ],
        'admin' => [
            'email'    => '',
            'subject'  => '',
            'name'     => '',
            'template' => [
                'logo'        => '',
                'footer_text' => '',
            ],
        ],
    ],
    'woo' => [
        'url'       => '',
        'ck'        => '',
        'cs'        => '',
        'option'    => [
            'version'       => 'wc/v3',
            'wp_api_prefix' => '/wp-json/',
        ],
        'v3_option' => [
            'version'       => 'v3',
            'wp_api_prefix' => '/wc-api/',
        ],
    ],
    'chat' => [
        'chatGPT'      => ['tokens' => []],
        'conversation' => ['limit' => 50],
    ],
    'payment' => [
        'gateway' => [
            'zarinpal' => [
                'merchant_id' => '',
                'sandbox'     => false,
                'url'         => ['request' => '', 'verify' => '', 'pg' => ''],
                'sandbox_url' => ['request' => '', 'verify' => ''],
            ],
        ],
        'callback_url' => '/',
    ],
    'avatar' => [
        'avatar_uri'        => $baseUrl . '/upload',
        'public_path'       => 'public/upload',
        'allowed_extension' => ['jpg', 'jpeg', 'png'],
        'mime_type'         => [],
        'allowed_size'      => ['min' => '1kB', 'max' => '2MB'],
    ],
    'translator' => [
        'locale'                    => 'fa_IR',
        'translation_file_patterns' => [
            [
                'type'     => 'phparray',
                'base_dir' => $basePath . '/module/User/language',
                'pattern'  => '%s.php',
            ],
        ],
    ],
    // Security config (required by Pi\Core SecurityMiddleware and related services)
    'security' => [
        'signature' => [
            'public_key'       => $basePath . '/data/keys/signature_public_key.pem',
            'private_key'      => $basePath . '/data/keys/signature_private_key.pem',
            'allowed_tables'   => ['user_account', 'role_account', 'permission_role'],
            'signature_fields' => [
                'user_account'    => ['id', 'name', 'identity', 'email', 'mobile', 'credential', 'status', 'multi_factor_status', 'multi_factor_secret'],
                'role_account'    => ['id', 'user_id', 'role', 'section'],
                'permission_role' => ['id', 'key', 'resource', 'section', 'module', 'role'],
            ],
        ],
        'ip' => [
            'is_active' => true,
            'whitelist' => ['127.0.0.1', '::1', '172.18.0.1', '192.168.1.1', '10.0.0.0/24'],
            'blacklist' => ['unknown', '203.0.113.5', '198.51.100.0/24'],
        ],
        'url' => [
            'is_active'     => true,
            'blacklist'     => [],
            'internal_urls' => [
                'http://localhost',
                'http://127.0.0.1',
                'https://localhost',
                'https://127.0.0.1',
            ],
        ],
        'origin' => ['is_active' => true, 'blacklist' => []],
        'method' => ['is_active' => true, 'allow_method' => ['POST', 'GET']],
        'xss' => ['is_active' => true, 'ignore_whitelist' => true, 'pattern_type' => 'standard'],
        'injection' => ['is_active' => true, 'ignore_whitelist' => true, 'pattern_type' => 'standard'],
        'inputValidation' => ['is_active' => true, 'ignore_whitelist' => true],
        'inputSizeLimit' => ['is_active' => true, 'max_input_size' => 1048576],
        'requestLimit' => [
            'is_active'        => true,
            'ignore_whitelist' => true,
            'max_requests'     => 100,
            'rate_limit'       => 60,
        ],
        'userData' => [
            'is_active'         => true,
            'ignore_whitelist'  => true,
            'geo_location_path' => $basePath . '/data/geoLite/GeoLite2-City.mmdb',
        ],
        'csrf' => [
            'is_active'  => true,
            'ignore_whitelist' => true,
            'check_list' => [],
        ],
        'header' => [
            'is_active' => true,
            'options'   => [
                'content_security_policy'    => "default-src 'self'",
                'strict_transport_security'   => 'max-age=31536000; includeSubDomains',
                'x_content_type_options'      => 'nosniff',
                'x_frame_options'             => 'DENY',
                'x_xss_protection'            => '1; mode=block',
                'referrer_policy'             => 'strict-origin-when-cross-origin',
                'permissions_policy'          => "geolocation=(), microphone=(), camera=(), fullscreen=(self), payment=(), usb=(), vibrate=(), sync-xhr=()",
                'cache_control'               => 'no-store, no-cache, must-revalidate, proxy-revalidate, private, max-age=0',
            ],
        ],
        'escape' => ['is_active' => true],
        'compress' => ['is_active' => true],
        'account' => ['attempts' => 5, 'ttl' => 3600],
    ],
    'log' => [
        'logger' => (new Laminas\Log\Logger())->addWriter(
            new Laminas\Log\Writer\Db(
                new Laminas\Db\Adapter\Adapter(
                    [
                        'driver'         => 'Pdo_Mysql',
                        'dsn'            => sprintf(
                            'mysql:dbname=%s;host=%s;port=%s;charset=utf8',
                            ('shahr_db'),
                            (getenv('MYSQL_DB_HOST') ?: 'localhost'),
                            (getenv('MYSQL_PORT') ?: '3306')
                        ),
                        'username'       => getenv('MYSQL_DB_USER') ?: '',
                        'password'       => getenv('MYSQL_DB_PASSWORD') ?: '',
                        'driver_options' => [
                            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_general_ci',
                            PDO::ATTR_PERSISTENT         => false,
                        ],
                        'options'        => [],
                    ]
                ),
                'logger_log',
                [
                    'timestamp' => 'date',
                    'priority' => 'type',
                    'message' => 'event',
                ]
            )
        ),
        'log_overflow_size' => 100,
        'log_trash_size' => 10
    ],
    'logger' => [
        // mysql, mongodb, file, disable
        'storage' => 'mysql',
        'mongodb' => [
            'uri'        => 'mongodb://localhost:27017',
            'database'   => 'XXX',
            'collection' => 'logger_system',
            'saveOptions' => [],
        ],
        'mysql' => [
            'driver'         => 'Pdo_Mysql',
            'dsn'            => sprintf(
                'mysql:dbname=%s;host=%s;port=%s;charset=utf8',
                ('shahr_db'),
                (getenv('MYSQL_DB_HOST') ?: 'localhost'),
                (getenv('MYSQL_PORT') ?: '3306')
            ),
            'username'       => getenv('MYSQL_DB_USER') ?: '',
            'password'       => getenv('MYSQL_DB_PASSWORD') ?: '',
            'driver_options' => [
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_general_ci',
                PDO::ATTR_PERSISTENT         => false,
            ],
            'options'        => [],
        ],
        'file' => [
            'path' => 'LOCAL_PATH',
            'date_format' => 'Y-m-d',
        ],
        'limitation' => [
            'cleanup'           => true,
            'maximum_allowed'   => 100000,
            'alert_threshold'   => 80,
            'cleanup_threshold' => 95,
            'cleanup_amount'    => 30,
        ],
        'forbidden_keys' => [
            'credential',
            'credentialColumn',
            'token',
            'access_token',
            'refresh_token',
            'token_payload',
            'permission',
            'HTTP_TOKEN',
            'Token',
            'controller',
            'middleware',
            'Laminas\Router\RouteMatch',
            'token_data',
            'current_token',
            'company_authorization',
            'media_authorization',
            'setting',
            'member',
            'package',
        ],
    ],
    'utility' => [
        'local' => 'fa_IR',
        'currency' => 'IRR',
        'timezone' => 'Asia/Tehran',
        'date_local' => 'fa_IR@calendar=persian',
        'date_type' => IntlDateFormatter::SHORT,
        'time_type' => IntlDateFormatter::NONE,
        'date_calendar' => IntlDateFormatter::TRADITIONAL,
        'date_pattern' => 'yyyy/MM/dd HH:mm:ss',
    ],
    'erm' => [
        'type'=>[],
        'rule' => [
            'target' => '',
            'import_type' => '',
            'export_type' => [],
        ],
        'task' => [
            'type' => '',
            'import_type' => '',
            'export_type' => [],
        ],
        'domain' => [
            'type' => [],
            'import_type' => '',
            'export_type' => [],
        ],
        'answers' => [
            'compliance' => [
                [
                    'value' => 'رعایت می شود',
                    'key' => 'yes',
                    'score' => '100',
                ],
                [
                    'value' => 'تاحدودی رعایت می شود',
                    'key' => 'partially',
                    'score' => '50',
                ],
                [
                    'value' => 'رعایت نمی شود',
                    'key' => 'no',
                    'score' => '0',
                ]
            ],
            'audit' => [
                [
                    'value' => 'مورد تایید است',
                    'key' => 'certified',
                    'score' => '100',
                ],
                [
                    'value' => 'تا حدودی مورد تایید است',
                    'key' => 'partially-certified',
                    'score' => '50',
                ],
                [
                    'value' => 'مورد تایید نیست',
                    'key' => 'not-certified',
                    'score' => '0',
                ],
            ],
            'maturity' => [
                [
                    'value' => 'اولیه',
                    'key' => 'initial',
                    'score' => '0',
                ],
                [
                    'value' => 'مدیریت شده',
                    'key' => 'managed',
                    'score' => '25',
                ],
                [
                    'value' => 'تعریف شده',
                    'key' => 'defined',
                    'score' => '50',
                ],
                [
                    'value' => 'به صورت کمی مدیریت شده',
                    'key' => 'partially-managed',
                    'score' => '75',
                ],
                [
                    'value' => 'بهینه شده',
                    'key' => 'optimized',
                    'score' => '100',
                ],
            ],
            'insurance' => [
                [
                    'value' => 'رعایت می شود',
                    'key' => 'yes',
                    'score' => '100',
                ],
                [
                    'value' => 'تاحدودی رعایت می شود',
                    'key' => 'partially',
                    'score' => '50',
                ],
                [
                    'value' => 'رعایت نمی شود',
                    'key' => 'no',
                    'score' => '0',
                ]
            ],

            'insurance-statement'=>[
                [
                    'value'=>'رعایت می شود',
                    'key'=>'yes',
                    'score'=>'100',
                ],
                [
                    'value'=>'تاحدودی رعایت می شود',
                    'key'=>'partially',
                    'score'=>'50',
                ],
                [
                    'value'=>'رعایت نمی شود',
                    'key'=>'no',
                    'score'=>'0',
                ]
            ],
            'insurance-comparison'=>[
                [
                    'value'=>'رعایت می شود',
                    'key'=>'yes',
                    'score'=>'100',
                ],
                [
                    'value'=>'تاحدودی رعایت می شود',
                    'key'=>'partially',
                    'score'=>'50',
                ],
                [
                    'value'=>'رعایت نمی شود',
                    'key'=>'no',
                    'score'=>'0',
                ]
            ],
        ],
        'directory' => [
            'download_path' => '/../../../../'
        ]
    ],
    'media' => [
        'authorization' => [
            'access' => ['private']
        ],
        'download_uri' => 'https://shahr.kerloper.com/upload',
        'stream_uri' => 'https://shahr.kerloper.com/upload',
        'private_path' => 'PRIVATE_PATH',
        'public_path' => 'public/upload',
        'allowed_extension' => [
            'jpg',
            'jpeg',
            'png',
            'pdf',
            'doc',
            'docx',
            'xls',
            'xlsx',
            'csv',
            'txt',
            'mp3',
            'mp4',
            'm4a',
            'wma',
            'gif',
        ],
        'mime_type' => [
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/csv',
            'application/vnd.oasis.opendocument.spreadsheet',
        ],
        'allowed_size' => [
            'min' => '1kB',
            'max' => '10MB',
        ],
        // S3 client options (credentials via AWS_ACCESS_KEY_ID, AWS_SECRET_ACCESS_KEY env or IAM)
        's3' => [
            'version' => 'latest',
            'region'  => getenv('AWS_REGION') ?: 'us-east-1',
        ],
    ],
];
