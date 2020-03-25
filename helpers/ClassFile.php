<?php

namespace steroids\core\helpers;

use yii\base\Exception;
use yii\helpers\StringHelper;

/**
 * @property-read string $name
 * @property-read string $dir
 * @property-read string $namespace
 * @property-read string $metaPath
 * @property-read \ReflectionClass $reflection
 */
class ClassFile
{
    const TYPE_MODULE = 'module';
    const TYPE_MODEL = 'model';
    const TYPE_FORM = 'form';
    const TYPE_ENUM = 'enum';
    const TYPE_CONTROLLER = 'controller';

    private $_reflection;

    /**
     * @var string
     */
    public $moduleId;

    /**
     * @var string
     */
    public $moduleDir;

    /**
     * @var string
     */
    public $path;

    /**
     * @var string
     */
    public $className;

    /**
     * @var string
     */
    public $type;

    public static function createByClass($className, $type)
    {
        $module = ModuleHelper::resolveModule($className);
        if (!$module) {
            throw new Exception('Cannot resolve module by class name: ' . $className);
        }

        $alias = '@' . str_replace('\\', '/', StringHelper::dirname($className));
        $name = StringHelper::basename($className);
        return new static([
            'path' => \Yii::getAlias($alias) . "/$name.php",
            'className' => $className,
            'moduleId' => $module->moduleId,
            'moduleDir' => $module->moduleDir,
            'type' => $type,
        ]);
    }

    /**
     * ClassFile constructor.
     * @param array $config
     */
    public function __construct($config = [])
    {
        foreach ($config as $key => $value) {
            $this->$key = $value;
        }
    }

    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter();
        }
    }

    public function getName()
    {
        return str_replace($this->namespace . '\\', '', $this->className);
    }

    /**
     * @return string
     */
    public function getDir()
    {
        return StringHelper::dirname($this->path);
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return StringHelper::dirname($this->className);
    }

    public function getMetaPath()
    {
        return preg_replace('/([^\\\\\/]+)\.php$/', 'meta/${1}Meta.php', $this->path);
    }

    /**
     * @return \ReflectionClass
     * @throws \ReflectionException
     */
    public function getReflection()
    {
        if (!$this->_reflection) {
            $this->_reflection = new \ReflectionClass($this->className);
        }
        return $this->_reflection;
    }
}
