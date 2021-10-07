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
        $property->setPhpType('string');
        $property->setFormat('date-time');
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
            [$attributeEntity->getName(), 'date', 'format' => $attributeEntity->getCustomProperty(self::OPTION_FORMAT) ?: 'php:Y-m-d H:i:s'],
        ];
    }
}
