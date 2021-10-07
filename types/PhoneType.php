<?php

namespace steroids\core\types;

use steroids\core\base\Type;
use yii\db\Schema;

class PhoneType extends Type
{
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
            [$attributeEntity->getName(), 'string', 'max' => 32],
        ];
    }
}
