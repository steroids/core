<?php

namespace steroids\core\types;

use steroids\core\base\Type;
use yii\db\Schema;

class EmailType extends Type
{
    /**
     * @inheritdoc
     */
    public function prepareSwaggerProperty($modelClass, $attribute, $property)
    {
        $property->setPhpType('string');
        $property->setFormat('email');
    }

    /**
     * @inheritdoc
     */
    public function giiDbType($attributeEntity)
    {
        return Schema::TYPE_STRING;
    }

    /**
     * @inheritdoc
     */
    public function giiRules($attributeEntity, &$useClasses = [])
    {
        return [
            [$attributeEntity->getName(), 'string', 'max' => 255],
            [$attributeEntity->getName(), 'email'],
        ];
    }
}
