<?php

namespace steroids\core\commands;

use yii\base\Module;
use yii\console\controllers\MigrateController;

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

    protected function getMigrationHistory($limit)
    {
        $migrations = parent::getMigrationHistory($limit);

        // Append library migrations with same class names
        foreach (array_keys($migrations) as $name) {
            $info = new \ReflectionClass($name);

            if (isset($this->migrationNamespaceExtends[$info->getNamespaceName()])) {
                $extendName = $this->migrationNamespaceExtends[$info->getNamespaceName()] . '\\' . $info->getShortName();
                if (class_exists($extendName)) {
                    $migrations[$extendName] = $migrations[$name];
                }
            }
        }

        return $migrations;
    }

    protected function getNewMigrations()
    {
        $newMigrations = parent::getNewMigrations();

        return $this->filterExtendedMigrations($newMigrations);
    }

    /**
     * For the given list of the migration classes make sure that if one migration extends another migration from the
     * steroids lib, then the parent migration will be excluded from the migrations list
     *
     * @example Suppose the migrations is following:
     * [
     *  'app\auth\migrations\M000000000001Auth', // extends steroids\auth\migrations\M000000000001Auth
     *  'steroids\auth\migrations\M000000000001Auth',
     *  'app\auth\migrations\M000000000001SomeMigration',
     * ]
     *
     * then the filtered list will be as following:
     * [
     *  'app\auth\migrations\M000000000001Auth', // extends steroids\auth\migrations\M000000000001Auth
     *  'app\auth\migrations\M000000000001SomeMigration',
     * ]
     *
     * @param $migrations
     * @return array
     * @throws \ReflectionException
     */
    private function filterExtendedMigrations($migrations)
    {
        $steroidsParentMigrations = [];

        foreach ($migrations as $name) {
            $info = new \ReflectionClass($name);
            $parentMigrationClassName = $info->getParentClass()->name;
            if (
                // if it's steroids' migration
                strpos($parentMigrationClassName, 'steroids') === 0
                // if it wasn't saved before
                && !in_array($parentMigrationClassName, $steroidsParentMigrations)
            ) {
                $steroidsParentMigrations[] = $info->getParentClass()->name;
            }
        }

        return array_filter($migrations, function ($migrationName) use ($steroidsParentMigrations) {
            return !in_array($migrationName, $steroidsParentMigrations);
        });
    }
}