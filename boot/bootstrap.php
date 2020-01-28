<?php

namespace steroids\core\boot;

$customConfig = require __DIR__ . '/preload.php';
$config = Boot::resolveConfig($customConfig);

// Run application
if (php_sapi_name() === 'cli') {
    exit((new \steroids\core\base\ConsoleApplication($config))->run());
} else {
    (new \steroids\core\base\WebApplication($config))->run();
}
