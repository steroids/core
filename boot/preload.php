<?php

namespace steroids\core\boot;

use steroids\core\helpers\ModuleHelper;

defined('STEROIDS_IS_CLI') || define('STEROIDS_IS_CLI', php_sapi_name() == 'cli');
defined('STEROIDS_ROOT_DIR') || define(
    'STEROIDS_ROOT_DIR',
    STEROIDS_IS_CLI ? dirname(realpath($_SERVER['argv'][0])) : dirname(dirname($_SERVER['SCRIPT_FILENAME']))
);

require_once __DIR__ . '/Boot.php';
require_once dirname(__DIR__) . '/helpers/ModuleHelper.php';

// Load custom config, if exists
$customConfig = Boot::safeLoadConfig(STEROIDS_ROOT_DIR . '/config.php');
$env = $customConfig['env'] ?? 'production';
unset($customConfig['env']);

// Define cli constants
if (STEROIDS_IS_CLI) {
    defined('STDIN') || define('STDIN', fopen('php://stdin', 'r'));
    defined('STDOUT') || define('STDOUT', fopen('php://stdout', 'w'));
}

// Define steroids constants
defined('STEROIDS_APP_DIR') || define('STEROIDS_APP_DIR', STEROIDS_ROOT_DIR . '/app');
defined('STEROIDS_APP_NAMESPACE') || define('STEROIDS_APP_NAMESPACE', 'app');
defined('STEROIDS_VENDOR_DIR') || define('STEROIDS_VENDOR_DIR', STEROIDS_ROOT_DIR . '/vendor');

// Define Yii constants
defined('YII_ENV') || define('YII_ENV', $env);
defined('YII_DEBUG') || define('YII_DEBUG', in_array(YII_ENV, ['dev', 'development', 'preview', 'stage']));
defined('YII_ENV_PROD') || define('YII_ENV_PROD', in_array(YII_ENV, ['preview', 'stage', 'beta', 'alpha', 'prod', 'production']));
defined('YII_ENV_DEV') || define('YII_ENV_DEV', in_array(YII_ENV, ['dev', 'development']));
defined('YII_ENV_TEST') || define('YII_ENV_TEST', YII_ENV === 'test');

// Init Yii autoloader
require(STEROIDS_VENDOR_DIR . '/autoload.php');
ModuleHelper::loadClass(STEROIDS_APP_DIR . '/core/base/Yii.php', 'Yii');

// Set alias to steroids and files dir (with generated or uploaded files)
\Yii::setAlias('@steroids', STEROIDS_VENDOR_DIR . '/steroids');
\Yii::setAlias('@files', STEROIDS_ROOT_DIR . '/' . (!YII_ENV_DEV ? '../' : '') . 'files');

return $customConfig;