<?php

namespace steroids\core\types;

use steroids\core\base\Type;
use yii\db\Schema;

class PasswordType extends Type
{
    public $min = YII_ENV_DEV ? 1 : 3;
    public $max = 255;

    public function getPhpType()
    {
        return static::PHP_STRING_TYPE;
    }

    /**
     * @inheritdoc
     */
    public function prepareFieldProps($modelClass, $attribute, &$props)
    {
        $props = array_merge(
            [
                'component' => 'PasswordField',
                'attribute' => $attribute,
            ],
            $props
        );
    }
    /**
     * @inheritdoc
     */
    public function prepareSwaggerProperty($modelClass, $attribute, &$property)
    {
        $property = array_merge(
            [
                'type' => 'string',
                'format' => 'password',
            ],
            $property
        );
    }

    /**
     * @inheritdoc
     */
    public function renderValue($model, $attribute, $item, $options = [])
    {
        return '********';
    }

    /**
     * @inheritdoc
     */
    public function giiDbType($attributeEntity)
    {
        return Schema::TYPE_STRING . '(' . $this->max . ')';
    }

    /**
     * @inheritdoc
     */
    public function giiRules($attributeEntity, &$useClasses = [])
    {
        return [
            [$attributeEntity->name, 'string', 'min' => $this->min, 'max' => $this->max],
        ];
    }
}
