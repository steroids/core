<?php

namespace steroids\core\types;

use steroids\core\base\Type;
use yii\db\Schema;

class PhoneType extends Type
{
    public function getPhpType()
    {
        return static::PHP_STRING_TYPE;
    }

    /**
     * @inheritdoc
     */
    public function prepareFieldProps($modelClass, $attribute, &$props)
    {
        $props = array_merge(
            [
                'component' => 'InputField',
                'attribute' => $attribute,
                'type' => 'phone',
            ],
            $props
        );
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
        // TODO Phone validator
        return [
            [$attributeEntity->name, 'string', 'max' => 32],
        ];
    }
}
