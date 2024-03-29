<?php

namespace steroids\core\components;

use steroids\core\base\Enum;
use steroids\core\base\FormModel;
use steroids\core\base\Model;
use steroids\core\base\Type;
use steroids\core\helpers\ClassFile;
use steroids\core\types\AutoTimeType;
use steroids\core\types\BooleanType;
use steroids\core\types\FloatType;
use steroids\core\types\MoneyType;
use steroids\core\types\DateTimeType;
use steroids\core\types\DateType;
use steroids\core\types\EnumType;
use steroids\core\types\FilesType;
use steroids\core\types\FileType;
use steroids\core\types\HtmlType;
use steroids\core\types\IntegerType;
use steroids\core\types\PrimaryKeyType;
use steroids\core\types\RelationType;
use steroids\core\types\SizeType;
use steroids\core\types\StringType;
use steroids\core\types\TextType;
use steroids\gii\forms\BackendFormEntity;
use steroids\gii\forms\BackendModelEntity;
use steroids\gii\helpers\GiiHelper;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/**
 * @property-read AutoTimeType $autoTime
 * @property-read BooleanType $boolean
 * @property-read MoneyType $money
 * @property-read DateTimeType $dateTime
 * @property-read DateType $date
 * @property-read FloatType $double
 * @property-read EnumType $enum
 * @property-read FilesType $files
 * @property-read FileType $file
 * @property-read HtmlType $html
 * @property-read IntegerType $integer
 * @property-read PrimaryKeyType $primaryKey
 * @property-read RelationType $relation
 * @property-read SizeType $size
 * @property-read StringType $string
 * @property-read TextType $text
 */
class Types extends Component
{
    const AUTO_TIME = 'autoTime';
    const BOOLEAN = 'boolean';
    const MONEY = 'money';
    const DATE_TIME = 'dateTime';
    const DATE = 'date';
    const DOUBLE = 'double';
    const ENUM = 'enum';
    const FILES = 'files';
    const FILE = 'file';
    const HTML = 'html';
    const INTEGER = 'integer';
    const PRIMARY_KEY = 'primaryKey';
    const RELATION = 'relation';
    const SIZE = 'size';
    const STRING = 'string';
    const TEXT = 'text';

    /**
     * @var Type[]
     */
    public $types = [];

    public function init()
    {
        parent::init();
        $this->types = array_merge($this->getDefaultTypes(), $this->types);
    }

    public function __get($name)
    {
        if (isset($this->types[$name])) {
            return $this->getType($name);
        }

        return parent::__get($name);
    }

    /**
     * @param string $name
     * @return Type|null
     */
    public function getType($name)
    {
        if (!isset($this->types[$name])) {
            return null;
        }

        if (is_array($this->types[$name]) || is_string($this->types[$name])) {
            $this->types[$name] = \Yii::createObject($this->types[$name]);
            $this->types[$name]->name = $name;
        }
        return $this->types[$name];
    }

    /**
     * @param Model|string $modelClass
     * @param string $attribute
     * @return null|Type
     */
    public function getTypeByModel($modelClass, $attribute)
    {
        if (is_object($modelClass)) {
            $modelClass = get_class($modelClass);
        }

        $metaItem = ArrayHelper::getValue($modelClass::meta(), $attribute, []);
        $appType = ArrayHelper::getValue($metaItem, 'appType', 'string');
        return $this->getType($appType);
    }

    /**
     * @return Type[]
     */
    public function getTypes()
    {
        return array_map(function ($name) {
            return $this->getType($name);
        }, array_keys($this->types));
    }

    public function getFrontendMeta($models = [], $enums = [])
    {
        $meta = [];
        $exports = [
            $this->exportModels($models),
            $this->exportEnums($enums),
        ];
        foreach ($exports as $data) {
            foreach ($data as $key => $item) {
                $meta[$key] = array_merge(
                    ArrayHelper::getValue($meta, $key, []),
                    $item
                );
            }
        }
        return $meta;
    }

    /**
     * @param array $item
     * @return Type|null
     * @throws Exception
     */
    protected function getTypeByItem($item)
    {
        $appType = !empty($item['appType']) ? $item['appType'] : 'string';
        $component = $this->getType($appType);
        if (!$component) {
            throw new Exception('Not found app type `' . $appType . '`');
        }

        return $component;
    }

    /**
     * @param Model $modelClass
     * @param string $attribute
     * @return array|null
     */
    protected function getMetaItem($modelClass, $attribute)
    {
        if (is_object($modelClass)) {
            $modelClass = get_class($modelClass);
        }

        $meta = $modelClass::meta();
        $attribute = Html::getAttributeName($attribute);

        return isset($meta[$attribute]) ? $meta[$attribute] : null;
    }

    protected function getDefaultTypes()
    {
        $types = [];
        foreach (scandir(__DIR__ . '/../types') as $file) {
            $name = preg_replace('/\.php$/', '', $file);
            $id = lcfirst(preg_replace('/Type$/', '', $name));
            $class = '\steroids\core\types\\' . $name;
            if (class_exists($class)) {
                $types[$id] = [
                    'class' => $class,
                ];
            }
        }
        return $types;
    }

    protected static function normalizeClassName($names)
    {
        $result = [];
        if ($names) {
            foreach ((array)$names as $name) {
                if (!is_string($name)) {
                    continue;
                }

                $name = str_replace('\\', '.', $name);
                $className = trim(str_replace('.', '\\', $name), '.');
                if (class_exists($className)) {
                    $result[$name] = $className;
                }
            }
        }
        return $result;
    }

    protected function exportEnums($names, $result = [])
    {
        foreach (static::normalizeClassName($names) as $name => $className) {
            if (is_subclass_of($className, Enum::class)) {
                // TODO Other data?
                /** @type Enum $className */
                $result[$name]['labels'] = $className::toFrontend();
            }
            if (is_subclass_of($className, Model::class)) {
                /** @type Model $className */
                $result[$name]['labels'] = $className::asEnum();
            }
        }

        return $result;
    }

    protected function exportModels($names, $result = [])
    {
        foreach (static::normalizeClassName($names) as $name => $className) {
            if (is_subclass_of($className, Model::class) || is_subclass_of($className, FormModel::class)) {
                /** @type Model $className */
                $entity = is_subclass_of($className, Model::class)
                    ? BackendModelEntity::findOne(ClassFile::createByClass($className, ClassFile::TYPE_MODEL))
                    : BackendFormEntity::findOne(ClassFile::createByClass($className, ClassFile::TYPE_FORM));
                if (!$entity) {
                    $result[$name] = null;
                    continue;
                }

                //$result[$name]['labels'] = $className::asEnum();
                $result[$name]['attributes'] = $entity->getJsFields(false);
                $result[$name]['searchFields'] = $entity->getJsFields(true);
                $result[$name]['formatters'] = $entity->getJsFormatters();
                $result[$name]['permissions'] = $entity->getStaticPermissions(\Yii::$app->user->identity);
                $result = static::exportModels(GiiHelper::findClassNamesInMeta($result[$name]), $result);
                $result = static::exportEnums(GiiHelper::findClassNamesInMeta($result[$name]), $result);
            }
        }

        return $result;
    }
}