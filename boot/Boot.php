<?php

namespace steroids\core\boot;

use Yii;
use steroids\core\helpers\ModuleHelper;
use yii\base\BootstrapInterface;
use yii\db\mysql\Schema;
use yii\helpers\ArrayHelper;

class Boot
{
    /**
     * @param string $path
     * @return array
     */
    public static function safeLoadConfig($path)
    {
        $config = [];
        if (file_exists($path)) {
            ob_start();
            $config = require $path;
            ob_end_clean();
        }
        return $config;
    }

    /**
     * Run this method before create application for get app config
     * @param array $customConfig
     * @return array
     */
    public static function resolveConfig($customConfig)
    {
        return ArrayHelper::merge(
            // Load main config
            static::safeLoadConfig(STEROIDS_APP_DIR . '/config/main.php'),

            // Load web/console config
            static::safeLoadConfig(STEROIDS_APP_DIR . '/config/' . (STEROIDS_IS_CLI && !YII_ENV_TEST ? 'console' : 'web') . '.php'),

            // Load environment config
            static::safeLoadConfig(STEROIDS_APP_DIR . '/config/env/' . YII_ENV . '.php'),

            // Merge with custom
            $customConfig
        );
    }

    /**
     * @param array $yiiCustom
     * @return array
     * @return array
     */
    public static function getWebConfig($yiiCustom)
    {
        return ArrayHelper::merge(
            [
                'components' => [
                    'request' => [
                        'parsers' => [
                            'application/json' => 'yii\web\JsonParser',
                        ],
                    ],
                ],
            ],
            $yiiCustom
        );
    }

    /**
     * @param array $yiiCustom
     * @return array
     * @throws \Exception
     * @throws \ReflectionException
     */
    public static function getConsoleConfig($yiiCustom)
    {
        return ArrayHelper::merge(
            [
                'controllerNamespace' => 'app\commands',
                'controllerMap' => [
                    'migrate' => [
                        'class' => '\steroids\core\commands\MigrateCommand',
                    ],
                    'steroids' => [
                        'class' => '\steroids\core\commands\SteroidsCommand',
                    ],
                ],
                'on beforeRequest' => [static::class, 'onConsoleBeforeRequest'],
            ],
            $yiiCustom
        );
    }

    /**
     * @param array $yiiCustom
     * @return array
     * @throws \Exception
     */
    public static function getMainConfig($yiiCustom)
    {
        $timeZone = ArrayHelper::getValue($yiiCustom, 'timeZone', 'UTC');

        $config = [
            'basePath' => STEROIDS_APP_DIR,
            'vendorPath' => STEROIDS_VENDOR_DIR,
            'runtimePath' => '@files/runtime',
            'timeZone' => $timeZone,
            'bootstrap' => [
                'log',
                'siteMap',
                'cors',
            ],
            'components' => [
                'authManager' => [
                    'class' => 'steroids\core\components\AuthManager',
                ],
                'i18n' => [
                    'translations' => [
                        'steroids*' => [
                            'class' => 'yii\i18n\PhpMessageSource',
                            'basePath' => '@steroids/messages',
                            'sourceLanguage' => 'ru',
                        ]
                    ],
                ],
                'assetManager' => [
                    'bundles' => false,
                    'basePath' => '@files/assets',
                ],
                'cache' => [
                    'class' => 'yii\caching\FileCache',
                ],
                'db' => [
                    'class' => 'yii\db\Connection',
                    'charset' => 'utf8',
                    'on afterOpen' => [static::class, 'onDbAfterOpen'],
                ],
                'formatter' => [
                    'defaultTimeZone' => $timeZone,
                ],
                'log' => [
                    'traceLevel' => YII_DEBUG ? 3 : 0,
                    'targets' => [
                        [
                            'class' => 'yii\log\FileTarget',
                            'levels' => ['error', 'warning'],
                        ],
                    ],
                ],
                'types' => [
                    'class' => 'steroids\core\components\Types',
                ],
                'siteMap' => [
                    'class' => 'steroids\core\components\SiteMap',
                ],
                'urlManager' => [
                    'class' => 'yii\web\UrlManager',
                    'showScriptName' => false,
                    'enablePrettyUrl' => true,
                ],
                'cors' => [
                    'class' => 'steroids\core\components\Cors',
                ],
                'errorHandler' => [
                    'errorView' => '@steroids/core/views/error.php',
                ],
            ],
        ];

        // Add debug module for development env
        if (YII_ENV_DEV) {
            $config['bootstrap'][] = 'debug';
            $config['modules']['debug'] = 'yii\debug\Module';
        }

        // Append modules
        foreach (ModuleHelper::findModules(STEROIDS_APP_DIR, STEROIDS_APP_NAMESPACE) as $module) {
            // Load class
            ModuleHelper::loadClass($module->path, $module->className);

            // Check add to bootstrap
            if (is_subclass_of($module->className, '\yii\base\BootstrapInterface')) {
                $config['bootstrap'][] = $module->moduleId;
            }

            // Append to config
            $path = 'modules.' . str_replace('.', '.modules.', $module->moduleId);
            ArrayHelper::setValue($config, $path, [
                'class' => $module->className,
            ]);
        }

        return ArrayHelper::merge($config, $yiiCustom);
    }

    public static function onDbAfterOpen($event)
    {
        if ($event->sender->schema instanceof Schema) {
            $event->sender->createCommand("SET time_zone='" . date('P') . "'")->execute();
        }
    }

    public static function onConsoleBeforeRequest()
    {
        Yii::setAlias('@tests', STEROIDS_ROOT_DIR . '/tests');
        Yii::setAlias('@webroot', STEROIDS_ROOT_DIR . '/public');
    }

}
