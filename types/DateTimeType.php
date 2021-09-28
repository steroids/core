<?php

namespace steroids\core\types;

use yii\db\Schema;

class DateTimeType extends DateType
{
    /**
     * @inheritdoc
     */
    public function prepareSwaggerProperty($modelClass, $attribute, $property)
    {
        $property->phpType = 'string';
        $property->format = 'date-time';
    }

    /**
     * @inheritdoc
     */
    public function giiDbType($attributeEntity)
    {
        return Schema::TYPE_DATETIME;
    }

    /**
     * @inheritdoc
     */
    public function giiRules($attributeEntity, &$useClasses = [])
    {
        return [
            [$attributeEntity->name, 'date', 'format' => $attributeEntity->format ?: 'php:Y-m-d H:i:s'],
        ];
    }
}
