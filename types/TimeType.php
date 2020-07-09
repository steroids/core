<?php

namespace steroids\core\types;

use steroids\core\base\Type;
use yii\db\Schema;

class TimeType extends Type
{
    const ATTRIBUTE_NAME = 'time';

    /**
     * @inheritdoc
     */
    public function prepareFieldProps($modelClass, $attribute, &$props)
    {
        $props = array_merge(
            [
                'component' => 'InputField',
                'attribute' => $attribute,
            ],
            $props
        );
    }

    /**
     * @inheritdoc
     */
    public function giiDbType($attributeEntity)
    {
        return Schema::TYPE_TIME;
    }

    /**
     * @inheritdoc
     */
    public function giiRules($attributeEntity, &$useClasses = [])
    {
        return [
            [$attributeEntity->name, 'date', 'format' => 'php:H:i:s'],
        ];
    }
}
