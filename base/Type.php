<?php

namespace steroids\core\base;

use steroids\core\interfaces\IGiiModelAttribute;
use steroids\core\interfaces\ISwaggerProperty;
use yii\base\BaseObject;
use yii\db\Schema;
use yii\helpers\ArrayHelper;

abstract class Type extends BaseObject
{
    /**
     * @var string
     */
    public $name;

    const PHP_INTEGER_TYPE = '?int';
    const PHP_FLOAT_TYPE = '?float';
    const PHP_STRING_TYPE = '?string';
    const PHP_BOOLEAN_TYPE = '?bool';
    const PHP_ARRAY_TYPE = '?array';

    /**
     * @return string
     */
    public function getPhpType()
    {
        return static::PHP_STRING_TYPE;
    }

    /**
     * @param Model $model
     * @param string $attribute
     * @return string|null
     */
    public function prepareFrontend($model, $attribute)
    {
        return null;
    }

    /**
     * @param array $item
     * @return array|null
     */
    public function prepareMeta($item)
    {
        return null;
    }



    /**
     * @param Model|FormModel|string $modelClass
     * @param string $attribute
     * @param array $props
     */
    public function prepareSearchFieldProps($modelClass, $attribute, &$props)
    {
    }

    /**
     * @param Model|FormModel|string $modelClass
     * @param string $attribute
     * @param array $props
     */
    public function prepareFieldProps($modelClass, $attribute, &$props)
    {
    }

    /**
     * @param Model|FormModel|string $modelClass
     * @param string $attribute
     * @param array $props
     */
    public function prepareFormatterProps($modelClass, $attribute, &$props)
    {
    }

    /**
     * @param IGiiModelAttribute $attributeEntity
     * @return array
     */
    public function getItems($attributeEntity)
    {
        return [];
    }

    /**
     * @param string $modelClass
     * @param string $attribute
     * @param ISwaggerProperty $property
     */
    public function prepareSwaggerProperty($modelClass, $attribute, $property)
    {
        $property->setPhpType('string');
    }

    /**
     * @param IGiiModelAttribute $attributeEntity
     * @param string[] $useClasses
     * @return array
     */
    public function giiRules($attributeEntity, &$useClasses = [])
    {
        return [
            [$attributeEntity->getName(), 'string'],
        ];
    }

    /**
     * @param IGiiModelAttribute $attributeEntity
     * @return array
     */
    public function giiBehaviors($attributeEntity)
    {
        return [];
    }

    /**
     * @param IGiiModelAttribute $attributeEntity
     * @return string|false
     */
    public function giiDbType($attributeEntity)
    {
        return Schema::TYPE_STRING;
    }

    /**
     * @return array
     */
    public function giiOptions()
    {
        return [];
    }

    /**
     * @param Model|FormModel|string $modelClass
     * @param string $attribute
     * @return array
     */
    protected function getOptions($modelClass, $attribute)
    {
        if (is_object($modelClass)) {
            $modelClass = get_class($modelClass);
        }

        return ArrayHelper::getValue($modelClass::meta(), $attribute, []);
    }
}
