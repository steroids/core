<?php

namespace steroids\core\types;

use steroids\core\base\Type;
use yii\db\Schema;

class HtmlType extends Type
{
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
            [$attributeEntity->getName(), 'string']
        ];
    }
}
