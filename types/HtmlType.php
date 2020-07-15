<?php

namespace steroids\core\types;

use steroids\core\base\Type;
use yii\db\Schema;

class HtmlType extends Type
{
    public $formatter = 'raw';

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
                'component' => 'HtmlField',
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
        return Schema::TYPE_TEXT;
    }

    /**
     * @inheritdoc
     */
    public function giiRules($attributeEntity, &$useClasses = [])
    {
        // TODO Html validator
        return [
            [$attributeEntity->name, 'string']
        ];
    }
}
