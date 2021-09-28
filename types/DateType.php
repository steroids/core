<?php

namespace steroids\core\types;

use steroids\core\base\Type;
use yii\db\Schema;

class DateType extends Type
{
    const OPTION_FORMAT = 'format';

    /**
     * @inheritdoc
     */
    public function prepareSwaggerProperty($modelClass, $attribute, $property)
    {
        $property->phpType = 'string';
        $property->format = 'date';
    }

    /**
     * @inheritdoc
     */
    public function giiDbType($attributeEntity)
    {
        return Schema::TYPE_DATE;
    }

    /**
     * @inheritdoc
     */
    public function giiRules($attributeEntity, &$useClasses = [])
    {
        return [
            [$attributeEntity->name, 'date', 'format' => 'php:Y-m-d'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function giiOptions()
    {
        return [
            [
                'attribute' => self::OPTION_FORMAT,
                'component' => 'InputField',
                'label' => 'Format',
            ],
        ];
    }
}
