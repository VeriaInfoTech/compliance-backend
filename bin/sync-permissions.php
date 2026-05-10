<?php
/**
 * Script to sync all module permissions from config files to database
 * 
 * Usage: php bin/sync-permissions.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Laminas\Mvc\Application;
use Pi\Core\Service\InstallerService;

// Bootstrap the application
$appConfig = require __DIR__ . '/../config/application.config.php';

// Disable config cache to avoid circular reference issues
$appConfig['module_listener_options']['config_cache_enabled'] = false;
$appConfig['module_listener_options']['module_map_cache_enabled'] = false;

$app = Application::init($appConfig);

// Get service manager
$serviceManager = $app->getServiceManager();

// Get installer service
$installerService = $serviceManager->get(InstallerService::class);

// Discover all modules that have a permission config (module/*/config/module.permission.php)
$moduleDir = __DIR__ . '/../module';
$modules   = [];
if (is_dir($moduleDir)) {
    foreach (scandir($moduleDir) as $entry) {
        if ($entry === '.' || $entry === '..' || !is_dir($moduleDir . '/' . $entry)) {
            continue;
        }
        $permissionFile = $moduleDir . '/' . $entry . '/config/module.permission.php';
        if (file_exists($permissionFile)) {
            $modules[strtolower($entry)] = $permissionFile;
        }
    }
}
ksort($modules);

echo "Starting permission sync (" . count($modules) . " modules with permission config)...\n\n";

foreach ($modules as $moduleName => $permissionFile) {
    if (!file_exists($permissionFile)) {
        echo "⚠️  Skipping {$moduleName}: Permission file not found at {$permissionFile}\n";
        continue;
    }
    
    try {
        echo "📦 Syncing permissions for module: {$moduleName}...\n";
        $permissionConfig = include $permissionFile;
        $installerService->installPermission($moduleName, $permissionConfig);
        echo "✅ Successfully synced permissions for {$moduleName}\n\n";
    } catch (\Exception $e) {
        echo "❌ Error syncing {$moduleName}: " . $e->getMessage() . "\n\n";
    }
}

echo "✨ Permission sync completed!\n";

