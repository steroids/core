<?php

namespace steroids\core\types;

use steroids\core\base\Type;

class SizeType extends Type
{
    public $formatter = 'shortSize';

    const ATTRIBUTE_NAME = 'size';

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