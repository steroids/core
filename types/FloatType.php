<?php

namespace steroids\core\types;

use steroids\gii\forms\BackendModelAttributeEntity;
use Yii;
use yii\db\Schema;
use yii\helpers\ArrayHelper;

class FloatType extends IntegerType
{
    const OPTION_SCALE = 'scale';

    public function getPhpType()
    {
        return static::PHP_FLOAT_TYPE;
    }

    /**
     * @inheritdoc
     */
    public function prepareSwaggerProperty($modelClass, $attribute, &$property)
    {
        $property = array_merge(
            [
                'type' => 'number',
            ],
            $property
        );
    }

    /**
     * @param BackendModelAttributeEntity $attributeEntity
     * @return string
     */
    public function giiDbType($attributeEntity)
    {
        $scale = ArrayHelper::getValue($attributeEntity->customProperties, self::OPTION_SCALE) ?: 2;
        return (string)Yii::$app->db->schema->createColumnSchemaBuilder(Schema::TYPE_DECIMAL, [19, $scale]);
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
     * @return array
     */
    public function giiOptions()
    {
        return [
            [
                'attribute' => self::OPTION_SCALE,
                'component' => 'NumberField',
                'label' => 'Scale',
            ]
        ];
    }
}
