<?php

namespace steroids\core\helpers;

use yii\base\Module;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\helpers\Json;
use yii\helpers\StringHelper;

require_once __DIR__ . '/ClassFile.php';

class ModuleHelper
{
    protected static $_modulesCache = [];

    /**
     * Scan directory to find modules and submodules
     * @param string $rootDir
     * @param string|null $baseNamespace
     * @param string $parentModuleId
     * @return ClassFile[]
     * @throws \Exception
     */
    public static function findModules($rootDir, $baseNamespace = null, $parentModuleId = '')
    {
        if (!isset(static::$_modulesCache[$rootDir])) {
            static::$_modulesCache[$rootDir] = [];
            foreach (FileHelper::findDirectories($rootDir, ['recursive' => false]) as $dir) {
                $namespace = $baseNamespace . '\\' . StringHelper::basename($dir);
                $module = static::resolveModule($dir, $namespace, $parentModuleId);
                if ($module) {
                    static::$_modulesCache[$rootDir] = array_merge(
                        static::$_modulesCache[$rootDir],
                        [$module],
                        static::findModules($module->dir, $module->namespace, $module->moduleId)
                    );
                }
            }
        }
        return static::$_modulesCache[$rootDir];
    }

    public static function findAppModuleClasses($module = null)
    {
        if (!$module) {
            $module = \Yii::$app;
        }

        $moduleClasses = [];
        foreach ($module->getModules() as $module) {
            $children = [];
            if (is_object($module)) {
                $moduleClasses[] = $module::className();
                $children = $module->getModules();
            } elseif (is_array($module) && isset($module['class'])) {
                $moduleClasses[] = $module['class'];
                $children = ArrayHelper::getValue($module, 'modules', []);
            } elseif (is_string($module)) {
                $moduleClasses[] = $module;
            }

            foreach ($children as $subModule) {
                $moduleClasses[] = [...$moduleClasses, static::findAppModuleClasses($subModule)];
            }
        }
        return $moduleClasses;
    }

    /**
     * @param $module
     * @param $type
     * @param $folder
     * @return ClassFile[]
     * @throws \Exception
     */
    public static function findModuleClasses($module, $type, $folder)
    {
        $module = static::resolveModule($module);
        if (!$module) {
            return [];
        }

        $classes = [];
        $files = FileHelper::findFiles($module->dir, [
            'only' => [
                $folder . '/*.php',
            ]
        ]);
        foreach ($files as $path) {
            $relativePath = StringHelper::dirname(str_replace($module->dir, '', $path));
            $namespace = $module->namespace . '\\' . trim(str_replace('/', '\\', $relativePath), '\\');
            $classes[] = new ClassFile([
                'moduleId' => $module->moduleId,
                'moduleDir' => dirname($module->path),
                'path' => $path,
                'className' => $namespace . '\\' . StringHelper::basename($path, '.php'),
                'type' => $type,
            ]);
        }
        return $classes;
    }

    /**
     * @param string|array $dirOrClassOrId
     * @param string|null $namespace
     * @param string|null $parentModuleId
     * @return ClassFile|null
     * @throws \Exception
     */
    public static function resolveModule($dirOrClassOrId, $namespace = null, $parentModuleId = null)
    {
        // Resolve dir by class name or module id
        if ($dirOrClassOrId instanceof ClassFile) {
            return $dirOrClassOrId;
        } elseif (strpos(realpath($dirOrClassOrId), realpath(STEROIDS_ROOT_DIR)) !== false) {
            $dir = $dirOrClassOrId;
        } elseif (is_subclass_of($dirOrClassOrId, Module::class)) {
            $dir = dirname((new \ReflectionClass($dirOrClassOrId))->getFileName());
        } else {
            if (!isset(static::$_modulesCache[STEROIDS_APP_DIR])) {
                static::findModules(STEROIDS_APP_DIR, STEROIDS_APP_NAMESPACE);
            }
            if (strpos($dirOrClassOrId, '\\') !== false) {
                $dirOrClassOrId = trim($dirOrClassOrId, '\\');

                /** @var ClassFile $finedModule */
                $finedModule = null;
                foreach (static::$_modulesCache as $modules) {
                    foreach ($modules as $module) {
                        /** @var ClassFile $module */
                        $extendNamespace = (new \ReflectionClass($module->className))->getParentClass()->getNamespaceName();
                        foreach ([$module->namespace, $extendNamespace] as $namespace) {
                            if (strpos($dirOrClassOrId, trim($namespace, '\\')) === 0
                                && (!$finedModule || strlen($finedModule->namespace) < strlen($namespace))
                            ) {
                                $finedModule = $module;
                                break;
                            }
                        }
                    }
                }
                return $finedModule;
            } else {
                foreach (static::$_modulesCache as $modules) {
                    foreach ($modules as $module) {
                        /** @var ClassFile $module */
                        if ($module->moduleId === $dirOrClassOrId) {
                            return $module;
                        }
                    }
                }
            }
            return null;
        }

        // Find dir composer.json
        if (file_exists("$dir/composer.json")) {
            $composer = Json::decode(file_get_contents($dir . '/composer.json'));
            $psr4 = ArrayHelper::getValue($composer, 'autoload.psr-4', []);
            foreach ($psr4 as $psrNamespace => $psrRelativePath) {
                $namespace = trim($psrNamespace, '\/');
                $dir = $dir . '/' . trim($psrRelativePath, '\/');
            }
        }

        // Auto detect namespace for app
        if ($namespace === null && strpos($dir, STEROIDS_APP_DIR) === 0) {
            $namespace = str_replace('/', '\\', mb_substr($dir, mb_strlen(STEROIDS_APP_DIR)));
            $namespace = STEROIDS_APP_NAMESPACE . '\\' . trim($namespace, '\\');
        }

        // Find module class
        foreach (scandir($dir) as $file) {
            if (preg_match('/([^\/]+)Module\.php$/', $file, $match)) {
                $vendorModules[] = $namespace . '\\' . $match[1] . 'Module';

                $class = $namespace . '\\' . $match[1] . 'Module';
                /*if (!is_subclass_of($class, '\steroids\core\base\Module')) {
                    throw new \Exception('Module class `' . $class . '` is not extends from `\steroids\core\base\Module`');
                }*/

                return new ClassFile([
                    'moduleId' => ($parentModuleId ? $parentModuleId . '.' : '') . lcfirst($match[1]),
                    'moduleDir' => $dir,
                    'path' => "$dir/$file",
                    'className' => $namespace . '\\' . $match[1] . 'Module',
                    'type' => ClassFile::TYPE_MODULE,
                ]);
            }
        }

        return null;
    }

    /**
     * Safety load class by path
     * @param $path
     * @param $name
     * @param bool $throwError
     * @return bool
     * @throws \Exception
     */
    public static function loadClass($path, $name, $throwError = true)
    {
        if (!file_exists($path)) {
            if ($throwError) {
                throw new \Exception('Not found module class file: ' . $path);
            }
            return false;
        }
        require_once $path;

        if (!class_exists($name)) {
            if ($throwError) {
                throw new \Exception('Not found module class: ' . $name);
            }
            return false;
        }

        return true;
    }
}
