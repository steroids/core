<?php

namespace steroids\core\types;

use steroids\core\base\Type;
use steroids\core\validators\ExtBooleanValidator;
use yii\db\Schema;

class BooleanType extends Type
{
    public function getPhpType()
    {
        return static::PHP_BOOLEAN_TYPE;
    }

    /**
     * @inheritdoc
     */
    public function giiDbType($attributeEntity)
    {
        return Schema::TYPE_BOOLEAN;
    }

    /**
     * @inheritdoc
     */
    public function prepareSwaggerProperty($modelClass, $attribute, $property)
    {
        $property->setPhpType('boolean');
    }

    /**
     * @inheritdoc
     */
    public function giiRules($attributeEntity, &$useClasses = [])
    {
        return [
            [$attributeEntity->getName(), ExtBooleanValidator::class],
        ];
    }
}
