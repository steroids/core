<?php

namespace steroids\core\types;

use steroids\gii\models\MetaItem;
use yii\db\Schema;

class MoneyWithCurrencyType extends CategorizedStringType
{
    public $currencyEnum;

    const ATTRIBUTE_NAME = 'moneyWithCurrency';

    /**
     * @inheritdoc
     */
    public function prepareFieldProps($modelClass, $attribute, &$props)
    {
        $props = array_merge(
            [
                'component' => 'CategorizedStringField',
                'attribute' => $attribute,
            ],
            $props
        );
    }

    /**
     * @inheritdoc
     */
    public function getGiiJsMetaItem($attributeEntity, $item, &$import = [])
    {
        if (!$attributeEntity->enumClassName) {
            $attributeEntity->enumClassName = $this->currencyEnum;
        }
        return parent::getGiiJsMetaItem($attributeEntity, $item, $import);
    }

    /**
     * @inheritdoc
     */
    public function getItems($attributeEntity)
    {
        if ($attributeEntity->refAttribute) {
            return [
                new MetaItem([
                    'metaClass' => $attributeEntity->metaClass,
                    'name' => $attributeEntity->refAttribute,
                    'appType' => 'enum',
                    'enumClassName' => $this->currencyEnum,
                    'publishToFrontend' => $attributeEntity->publishToFrontend,
                ]),
            ];
        }
        return [];
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
                'attribute' => self::OPTION_REF_ATTRIBUTE,
                'component' => 'InputField',
                'label' => 'Currency attribute',
                /*'style' => [
                    'width' => '120px'
                ]*/
            ],
            [
                'attribute' => self::OPTION_CLASS_NAME,
                'component' => 'InputField',
                'label' => 'Enum',
                /*'list' => ArrayHelper::getColumn(EnumClass::findAll(), 'className'),
                'style' => [
                    'width' => '80px'
                ]*/
            ],
        ];
    }

}
