<?php

namespace steroids\core\types;

use steroids\core\base\Model;
use steroids\core\base\Type;
use Yii;
use yii\db\Schema;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class RelationType extends Type
{
    const OPTION_RELATION_NAME = 'relationName';

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
                'autoComplete' => true,
                'dataProvider' => [
                    'action' => '', // TODO
                ],
            ],
            $props
        );
    }

    /**
     * @inheritdoc
     */
    public function giiDbType($attributeEntity)
    {
        $relationName = $attributeEntity->getCustomProperty(self::OPTION_RELATION_NAME);
        $relation = $attributeEntity->modelEntity->getRelationEntity($relationName);
        return $relation && $relation->isHasOne ? Schema::TYPE_INTEGER : false;
    }

    /**
     * @inheritdoc
     */
    public function giiRules($attributeEntity, &$useClasses = [])
    {
        $relationName = $attributeEntity->getCustomProperty(self::OPTION_RELATION_NAME);
        $relation = $attributeEntity->modelEntity->getRelationEntity($relationName);
        if ($relation && $relation->isHasOne) {
            return [
                [$attributeEntity->name, 'integer'],
            ];
        }
        return false;
    }

    /**
     * @inheritdoc
     */
    public function giiBehaviors($attributeEntity)
    {
        return [];
    }

    /**
     * @return array
     */
    public function giiOptions()
    {
        return [
            [
                'attribute' => self::OPTION_RELATION_NAME,
                'component' => 'InputField',
                'label' => 'Relation name',
                'list' => 'relations',
            ],
        ];
    }

}
