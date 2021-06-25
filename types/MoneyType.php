<?php

namespace steroids\core\types;

use steroids\core\base\Type;
use yii\db\Schema;

class MoneyType extends Type
{
    const OPTION_CURRENCY = 'currencyCode';

    public function getPhpType()
    {
        return static::PHP_INTEGER_TYPE;
    }

    /**
     * @inheritdoc
     */
    public function giiDbType($attributeEntity)
    {
        return Schema::TYPE_DECIMAL . '(19, 4)';
    }

    /**
     * @inheritdoc
     */
    public function giiRules($attributeEntity, &$useClasses = [])
    {
        return [
            [$attributeEntity->name, 'number'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function giiOptions()
    {
        return [
            [
                'attribute' => self::OPTION_CURRENCY,
                'component' => 'InputField',
                'label' => 'Currency',
                //'list' => ['RUB', 'USD', 'EUR', 'BTC', 'XBT', 'YEN', 'JPY', 'GBP'],
            ],
        ];
    }
}
