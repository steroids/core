<?php

namespace steroids\core\types;

use steroids\file\models\File;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

class FilesType extends RelationType
{
    public function getPhpType()
    {
        return static::PHP_ARRAY_TYPE;
    }

    /**
     * @inheritdoc
     */
    /*public function getFieldData($item, $params)
    {
        $initialFiles = [];
        $files = File::findAll(['id' => ArrayHelper::getValue($params, 'fileIds', [])]);
        foreach ($files as $file) {
            $initialFiles[] = [
                'uid' => $file->uid,
                'path' => $file->title,
                'type' => $file->fileMimeType,
                'bytesUploaded' => $file->fileSize,
                'bytesUploadEnd' => $file->fileSize,
                'bytesTotal' => $file->fileSize,
                'resultHttpMessage' => $file->getExtendedAttributes(ArrayHelper::getValue($params, 'processor')),
            ];
        }
        return [
            'initialFiles' => !empty($initialFiles) ? $initialFiles : null,
        ];
    }*/

    /**
     * @inheritdoc
     */
    public function giiDbType($attributeEntity)
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function prepareSwaggerProperty($modelClass, $attribute, $property)
    {
        $property->setPhpType('integer');
        $property->setIsArray(true);
    }

    /**
     * @inheritdoc
     */
    public function giiRules($attributeEntity, &$useClasses = [])
    {
        return [
            [$attributeEntity->getName(), 'each', 'rule' => ['integer']],
        ];
    }

}
