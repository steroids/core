<?php

namespace steroids\core\types;

use steroids\core\base\Enum;

class EnumListType extends EnumType
{
    public function getPhpType()
    {
        return static::PHP_ARRAY_TYPE;
    }

    /**
     * @inheritdoc
     */
    public function prepareFieldProps($modelClass, $attribute, &$props)
    {
        $props = array_merge(
            [
                'component' => 'DropDownField',
                'attribute' => $attribute,
                'multiple' => true,
            ],
            $props
        );
    }

    /**
     * @inheritdoc
     */
    public function giiDbType($attributeEntity)
    {
        return 'varchar(255)[]';
    }

    public function giiRules($attributeEntity, &$useClasses = [])
    {
        /** @var Enum $className */
        $className = $attributeEntity->enumClassName;

        //TODO return "['in', 'range' => $className::getKeys()]";

        return [];
    }
}
