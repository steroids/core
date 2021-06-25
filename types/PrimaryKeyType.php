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
     * @param string $modelClass
     * @param string $attribute
     * @param string $property
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
