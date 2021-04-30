<?php

namespace steroids\core\tests\traits;

use steroids\core\base\Model;
use steroids\core\exceptions\ModelSaveException;

trait ModelsCleanupTrait
{
    public array $savedModels = [];

    /**
     * @param Model $model
     * @return Model
     * @throws ModelSaveException
     */
    public function saveModel(Model $model)
    {
        $model->saveOrPanic();

        array_push($this->savedModels, $model);

        return $model;
    }

    public function tearDown(): void
    {
        parent::tearDown();

        foreach ($this->savedModels as $model) {
            $model->deleteOrPanic();
        }
    }
}