<?php

namespace steroids\core\types;

use steroids\core\base\Enum;
use steroids\core\base\FormModel;
use steroids\core\base\Model;
use steroids\core\base\Type;
use steroids\core\base\ValueExpression;
use steroids\gii\forms\BackendEnumEntity;
use yii\db\Schema;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

class EnumType extends Type
{
    const OPTION_CLASS_NAME = 'enumClassName';

    /**
     * @inheritDoc
     */
    public function prepareMeta($item)
    {
        $enumClass = ArrayHelper::getValue($item, self::OPTION_CLASS_NAME);
        return [
            'enumClassName' => trim(str_replace('\\', '.', $enumClass), '.') ?: null,
        ];
    }

    /**
     * @inheritdoc
     */
    public function prepareSwaggerProperty($modelClass, $attribute, $property)
    {
        /** @var Enum $enumClass */
        $enumClass = ArrayHelper::getValue($this->getOptions($modelClass, $attribute), self::OPTION_CLASS_NAME);

        $property->setPhpType('string');
        if ($enumClass) {
            $property->setEnum($enumClass::getKeys());
        }
    }

    /**
     * @param Model|FormModel|string $modelClass
     * @param string $attribute
     * @return mixed
     * @throws \ReflectionException
     */
    protected function getItemsProperty($modelClass, $attribute)
    {
        /** @var Enum $enumClass */
        $enumClass = ArrayHelper::getValue($this->getOptions($modelClass, $attribute), self::OPTION_CLASS_NAME);
        return $enumClass ? trim(str_replace('\\', '.', $enumClass), '.') : null;
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
        /** @var Enum $enumClass */
        $enumClass = $attributeEntity->getCustomProperty(self::OPTION_CLASS_NAME);
        if (!$enumClass) {
            return [
                [$attributeEntity->getName(), 'string'],
            ];
        }

        $shortClassName = StringHelper::basename($enumClass);
        $useClasses[] = $enumClass;

        return [
            [$attributeEntity->getName(), 'in', 'range' => new ValueExpression("$shortClassName::getKeys()")],
        ];
    }

    /**
     * @return array
     * @throws \ReflectionException
     */
    public function giiOptions()
    {
        return [
            [
                'attribute' => self::OPTION_CLASS_NAME,
                'component' => 'AutoCompleteField',
                'label' => 'Enum Class',
                'items' => ArrayHelper::getColumn(BackendEnumEntity::findAll(), 'className'),
            ]
        ];
    }
}
