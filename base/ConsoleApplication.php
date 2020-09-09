<?php

namespace steroids\core\base;

use steroids\core\helpers\ModuleHelper;
use yii\console\Application;
use yii\helpers\Inflector;

class ConsoleApplication extends Application
{
    /**
     * Enable scan commands classes in application modules
     * @var bool
     */
    public bool $autoScanCommands = true;

    /**
     * Initialize the application.
     */
    public function init()
    {
        parent::init();

        if ($this->autoScanCommands) {
            foreach (ModuleHelper::findAppModuleClasses() as $moduleClass) {
                // Get class info
                $classFile = ModuleHelper::resolveModule($moduleClass);
                if (!$classFile) {
                    continue;
                }

                // Get commands dir
                $commandsDir = $classFile->dir . DIRECTORY_SEPARATOR . 'commands';
                if (!is_dir($commandsDir)) {
                    continue;
                }

                // Scan commands
                foreach (scandir($commandsDir) as $fileName) {
                    if (!preg_match('/^((.+)Command)\.php$/', $fileName, $match)) {
                        continue;
                    }

                    // Add to controller map
                    $id = Inflector::camel2id($match[2]);
                    $className = $classFile->namespace . '\\commands\\' . $match[1];
                    $this->controllerMap[$id] = $className;
                }
            }
        }
    }

    public function coreComponents()
    {
        $components = parent::coreComponents();
        unset($components['mailer']);
        return $components;
    }
}
