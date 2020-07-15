<?php

namespace steroids\core\types;

use steroids\core\base\Type;

class SizeType extends Type
{
    public $formatter = 'shortSize';

    public function getPhpType()
    {
        return static::PHP_INTEGER_TYPE;
    }

    /**
     * @inheritdoc
     */
    public function giiRules($attributeEntity, &$useClasses = [])
    {
        return [
            [$attributeEntity->name, 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function prepareSwaggerProperty($modelClass, $attribute, &$property)
    {
        $property = array_merge(
            [
                'type' => 'number',
            ],
            $property
        );
    }
}