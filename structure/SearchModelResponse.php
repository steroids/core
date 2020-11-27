<?php

namespace steroids\core\structure;

use yii\base\BaseObject;

class SearchModelResponse extends BaseObject
{
    public int $total = 0;

    public array $items = [];

    public ?array $meta;

    public array $errors = [];

    public function toFrontend(): array
    {
        $asArray = [
            'total' => $this->total,
            'items' => $this->items,
            'meta' => $this->meta,
        ];

        if (!empty($this->errors)) {
            $asArray['errors'] = $this->errors;
        }

        return $asArray;
    }
}
