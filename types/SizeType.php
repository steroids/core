<?php

namespace steroids\core\types;

use steroids\core\base\Type;

class SizeType extends Type
{
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
    public function prepareSwaggerProperty($modelClass, $attribute, $property)
    {
        $property->phpType = 'integer';
    }
}