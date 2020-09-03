<?php

namespace steroids\core\base;

use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

/**
 * @package steroids\core\base
 * @property-read string|null $libraryBasePath
 */
class Module extends \yii\base\Module
{
    /**
     * Models map for customise classes
     * @var array
     */
    public array $classesMap;

    /**
     * @return static
     * @throws Exception
     */
    public static function getInstance()
    {
        if (!preg_match('/([^\\\]+)Module$/', static::className(), $match)) {
            throw new Exception('Cannot auto get module id by class name: ' . static::className());
        }

        /** @var Module $module */
        $module = \Yii::$app->getModule(lcfirst($match[1]));
        return $module;
    }

    /**
     * Part of site menu for this module
     * @return array
     */
    public static function siteMap()
    {
        return [];
    }

    /**
     * @param string $className
     * @return string
     * @throws Exception
     */
    public static function resolveClass($className)
    {
        $module = static::getInstance();
        if (!empty($module->classesMap)) {
            $className = rtrim($className, '\\');
            $className = ArrayHelper::getValue($module->classesMap, $className)
                ?: ArrayHelper::getValue($module->classesMap, '\\' . $className)
                    ?: $className;
        }
        return $className;
    }

    /**
     * @param string $className
     * @param array $config
     * @return mixed
     */
    public static function instantiateClass($className, $config = null)
    {
        $className = static::resolveClass($className);
        return $config ? new $className($config) : new $className();
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        // Layout for admin modules (as submodule in application modules)
        if ($this->id === 'admin') {
            $this->layout = '@app/core/admin/layouts/web';
        }

        parent::init();

        $this->initCoreComponents();
    }

    public function getLibraryBasePath()
    {
        $info = (new \ReflectionClass($this))->getParentClass();
        if ($info !== get_class($this)) {
            return dirname($info->getFileName());
        }
        return null;
    }

    protected function initCoreComponents()
    {
        $coreComponents = $this->coreComponents();
        foreach ($coreComponents as $id => $config) {
            if (is_string($this->$id)) {
                $config = ['class' => $this->$id];
            } elseif (is_array($this->$id)) {
                $config = ArrayHelper::merge($config, $this->$id);
            }
            $this->$id = \Yii::createObject($config);
        }
    }

    protected function coreComponents()
    {
        return [];
    }

}