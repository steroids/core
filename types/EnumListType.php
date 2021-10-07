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
    public function giiDbType($attributeEntity)
    {
        return 'varchar(255)[]';
    }

    public function giiRules($attributeEntity, &$useClasses = [])
    {
        /** @var Enum $className */
        $className = $attributeEntity->getCustomProperty(self::OPTION_CLASS_NAME);

        //TODO return "['in', 'range' => $className::getKeys()]";

        return [];
    }
}
