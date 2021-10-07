<?php

namespace steroids\core\types;

use steroids\core\base\Type;
use yii\db\Schema;

class PasswordType extends Type
{
    public $min = YII_ENV_DEV ? 1 : 3;
    public $max = 255;

    /**
     * @inheritdoc
     */
    public function prepareSwaggerProperty($modelClass, $attribute, $property)
    {
        $property->setPhpType('string');
        $property->setFormat('password');
    }

    /**
     * @inheritdoc
     */
    public function giiDbType($attributeEntity)
    {
        return Schema::TYPE_STRING . '(' . $this->max . ')';
    }

    /**
     * @inheritdoc
     */
    public function giiRules($attributeEntity, &$useClasses = [])
    {
        return [
            [$attributeEntity->getName(), 'string', 'min' => $this->min, 'max' => $this->max],
        ];
    }
}
