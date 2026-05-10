<?php
/**
 * همگام‌سازی پرمیژن‌های ماژول Erm با دیتابیس (فقط erm، بدون بقیهٔ ماژول‌ها).
 *
 * اجرا از ریشهٔ پروژهٔ بک‌اند:
 *   php module/Erm/bin/sync-permissions.php
 *
 * برای همگام‌سازی همهٔ ماژول‌ها:
 *   php bin/sync-permissions.php
 */

declare(strict_types=1);

require dirname(__DIR__, 3) . '/vendor/autoload.php';

use Laminas\Mvc\Application;
use Pi\Core\Service\InstallerService;

$appConfig = require dirname(__DIR__, 3) . '/config/application.config.php';
$appConfig['module_listener_options']['config_cache_enabled'] = false;
$appConfig['module_listener_options']['module_map_cache_enabled'] = false;

$app = Application::init($appConfig);
$installerService = $app->getServiceManager()->get(InstallerService::class);

$permissionFile = dirname(__DIR__) . '/config/module.permission.php';
if (!is_file($permissionFile)) {
    fwrite(STDERR, "Permission config not found: {$permissionFile}\n");
    exit(1);
}

echo "Syncing Erm permissions from module.permission.php...\n";
$installerService->installPermission('erm', include $permissionFile);
echo "Done.\n";
