<?php

namespace steroids\core\types;

use steroids\core\base\Type;
use yii\db\Schema;

class EmailType extends Type
{
    public $formatter = 'email';

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
                'component' => 'InputField',
                'attribute' => $attribute,
                'type' => 'email',
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
                'format' => 'email',
            ],
            $property
        );
    }

    /**
     * @inheritdoc
     */
    public function giiDbType($attributeEntity)
    {
        return Schema::TYPE_STRING;
    }

    /**
     * @inheritdoc
     */
    public function giiRules($attributeEntity, &$useClasses = [])
    {
        return [
            [$attributeEntity->name, 'string', 'max' => 255],
            [$attributeEntity->name, 'email'],
        ];
    }
}
