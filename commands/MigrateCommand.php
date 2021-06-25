<?php

namespace steroids\core\commands;

use yii\base\Module;
use yii\console\controllers\MigrateController;
use ReflectionException;

class MigrateCommand extends MigrateController
{
    public $migrationPath = null;
    protected $migrationNamespaceExtends = [];

    public function beforeAction($action)
    {
        $appPath = \Yii::getAlias('@app');

        // Set migration namespaces
        foreach (scandir($appPath) as $dirName) {
            $namespace = 'app\\' . $dirName . '\migrations';

            $this->migrationNamespaces[] = $namespace;
            \Yii::setAlias('@' . $namespace, $appPath . '/' . $dirName . '/migrations');
        }

        $this->scanNamespacesFromModules(\Yii::$app);

        return parent::beforeAction($action);
    }

    protected function scanNamespacesFromModules($module)
    {
        foreach ($module->modules as $module) {
            $info = new \ReflectionClass(is_object($module) ? get_class($module) : $module['class']);

            // App migrations
            $namespace = $info->getNamespaceName() . '\\migrations';
            $dir = dirname($info->getFileName()) . '/migrations';
            if (is_dir($dir)) {
                $this->migrationNamespaces[] = $namespace;
                \Yii::setAlias('@' . $namespace, $dir);
            }

            // Steroids (or other lib) migrations
            $parentInfo = $info->getParentClass();
            $libNamespace = $parentInfo->getNamespaceName() . '\\migrations';
            $libDir = dirname($parentInfo->getFileName()) . '/migrations';
            if (is_dir($libDir)) {
                $this->migrationNamespaces[] = $libNamespace;
                \Yii::setAlias('@' . $libNamespace, $libDir);
            }

            // Store extend mapping
            if (is_dir($dir) && is_dir($libDir)) {
                $this->migrationNamespaceExtends[$namespace] = $libNamespace;
            }

            if ($module instanceof Module) {
                $this->scanNamespacesFromModules($module);
            }
        }
    }

    /**
     * @inheritDoc
     */
    protected function getMigrationHistory($limit)
    {
        $migrations = parent::getMigrationHistory($limit);

        // Append library migrations, if it is not exists in application
        foreach (array_keys($migrations) as $name) {
            $extendName = $this->getExtendedName($name, $this->migrationNamespaceExtends);
            if ($extendName) {
                $migrations[$extendName] = $migrations[$name];
            }
        }

        return $migrations;
    }

    /**
     * @inheritDoc
     * @throws ReflectionException
     */
    protected function getNewMigrations()
    {
        $migrations = parent::getNewMigrations();

        // Replace library migrations by application migrations
        $migrations = array_filter($migrations, function ($name) {
            return !$this->getExtendedName($name, array_flip($this->migrationNamespaceExtends));
        });

        return $migrations;
    }

    /**
     * Get migration class from map
     * @param string $name
     * @param $map
     * @return string|null
     * @throws ReflectionException
     */
    protected function getExtendedName(string $name, $map)
    {
        $info = new \ReflectionClass($name);
        if (isset($map[$info->getNamespaceName()])) {
            $extendName = $map[$info->getNamespaceName()] . '\\' . $info->getShortName();
            if (class_exists($extendName)) {
                return $extendName;
            }
        }
        return null;
    }
}