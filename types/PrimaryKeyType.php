<?php

namespace steroids\core\types;

use steroids\core\base\Type;
use yii\db\Schema;

class PrimaryKeyType extends Type
{
    public function getPhpType()
    {
        return static::PHP_INTEGER_TYPE;
    }

    /**
     * @inheritDoc
     */
    public function prepareSwaggerProperty($modelClass, $attribute, $property)
    {
        $property->phpType = 'integer';
    }

    /**
     * @inheritdoc
     */
    public function giiDbType($attributeEntity)
    {
        return Schema::TYPE_PK;
    }

    /**
     * @inheritdoc
     */
    public function giiRules($attributeEntity, &$useClasses = [])
    {
        return false;
    }
}
