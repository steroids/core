<?php

namespace steroids\core\types;

use steroids\core\base\Type;
use yii\db\Schema;

class IntegerType extends Type
{
    public function getPhpType()
    {
        return static::PHP_INTEGER_TYPE;
    }

    /**
     * @inheritdoc
     */
    public function giiDbType($attributeEntity)
    {
        return Schema::TYPE_INTEGER;
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

    /**
     * @inheritdoc
     */
    public function giiRules($attributeEntity, &$useClasses = [])
    {
        return [
            [$attributeEntity->name, 'integer'],
        ];
    }

}
