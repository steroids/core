<?php

namespace steroids\core\traits;

use steroids\auth\UserInterface;
use steroids\core\base\BaseSchema;
use steroids\core\base\Model;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use \Exception;

trait MetaTrait
{
    /**
     * @return array
     */
    public static function meta()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        $labels = [];
        foreach (static::meta() as $attribute => $item) {
            if (isset($item['label']) && is_string($item['label'])) {
                $labels[$attribute] = $item['label'];
            }
        }
        return $labels;
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        $hints = [];
        foreach (static::meta() as $attribute => $item) {
            if (isset($item['hint']) && is_string($item['hint'])) {
                $hints[$attribute] = $item['hint'];
            }
        }
        return $hints;
    }

    /**
     * @param array $data
     * @param array $scopes
     * @return array
     * @throws \Exception
     */
    protected static function getFieldsByScopes(array $data, array $scopes): array
    {
        // Add default scope
        array_unshift($scopes, self::SCOPE_DEFAULT);

        $fields = [];
        foreach ($scopes as $scope) {
            $fields = array_merge($fields, ArrayHelper::getValue($data, $scope, []));
        }
        return $fields;
    }

    /**
     * @param $model
     * @param null $fields
     * @param UserInterface|null $user
     * @param array $scopes
     * @return array|array[]|null[]|Model[]|\steroids\core\base\Model[][]|null
     * @throws InvalidConfigException
     * @throws Exception
     */
    public static function anyToFrontend($model, $fields = null, UserInterface $user = null, array $scopes = [])
    {
        $fields = $fields ? (array)$fields : ['*'];

        // Detect array
        if (is_array($model)) {
            return array_map(function ($item) use ($fields, $user, $scopes) {
                return static::anyToFrontend($item, $fields, $user, $scopes);
            }, $model);
        }

        // Detect empty
        if (!$model) {
            return $model;
        }

        // Scalar
        if (!is_object($model)) {
            return $model;
        }

        // Detect *
        foreach ($fields as $key => $name) {
            // Syntax: *
            if ($name === '*') {
                unset($fields[$key]);
                $fields = array_merge(
                    method_exists($model, 'frontendFields')
                        ? static::getFieldsByScopes($model->frontendFields($user), $scopes)
                        : $model->fields(),
                    $fields
                );

                if ($model instanceof BaseSchema && $model->model instanceof Model) {
                    $index = array_search('*', $fields);
                    if ($index !== false) {
                        unset($fields[$index]);
                        $fields = array_merge(
                            $fields,
                            method_exists($model->model, 'frontendFields')
                                ? static::getFieldsByScopes($model->model->frontendFields($user), $scopes)
                                : $model->model->fields()
                        );
                    }
                }
                break;
            }
        }

        $result = [];

        // Detect * => model.*
        foreach ($fields as $key => $name) {
            // Syntax: * => model.*
            if ($key === '*' && preg_match('/\.*$/', $name) !== false) {
                unset($fields[$key]);

                /** @var Model $subModel */
                $attribute = substr($name, 0, -2);
                $subModel = ArrayHelper::getValue($model, $attribute);
                if ($subModel) {
                    $subModelFields = method_exists($subModel, 'frontendFields')
                        ? static::getFieldsByScopes($subModel->frontendFields($user), $scopes)
                        : $subModel->fields();
                    foreach ($subModelFields as $key => $name) {
                        $key = is_int($key) ? $name : $key;
                        $fields[$key] = $attribute . '.' . $name;
                    }
                    //$result = array_merge($result, static::anyToFrontend($subModel));
                }
            }
        }

        // Export
        foreach ($fields as $key => $name) {
            $key = is_int($key) ? $name : $key;

            if (!is_string($key)) {
                throw new InvalidConfigException('Wrong fields format for model "' . get_class($model) . '"');
            }

            // Detect path
            if (is_string($name) && strpos($name, '.') !== false) {
                $parts = explode('.', $name);
                $name = array_pop($parts);
                $item = ArrayHelper::getValue($model, $parts);
            } else {
                $item = $model;
            }

            // BaseScheme logic
            if (is_string($name) && $item instanceof BaseSchema) {
                if ($item->canGetProperty($name, true, false)) {
                    $result[$key] = static::anyToFrontend($item->$name, null, $user, $scopes);
                    continue;
                } else {
                    $item = $item->model;
                }
            }

            // Standard model logic
            if (is_callable($name) && (!is_string($name) || !function_exists($name))) {
                $result[$key] = static::anyToFrontend(call_user_func($name, $item), null, $user, $scopes);
            } elseif (is_array($name)) {
                $result[$key] = static::anyToFrontend(ArrayHelper::getValue($item, $key), $name, $user, $scopes);
            } else {
                $result[$key] = static::anyToFrontend(ArrayHelper::getValue($item, $name), null, $user, $scopes);
            }
        }
        return $result;
    }

    /**
     * Note: when user param is supplied, fields in result will be filtered
     * according to the user permissions.
     *
     * @param array|string|null $fields
     * @param Model $user
     * @param string[] $scopes
     * @return array
     * @throws InvalidConfigException
     */
    public function toFrontend($fields = null, $user = null, array $scopes = [])
    {
        $self = $this;
        if (method_exists($this, 'createSchema') && method_exists($this, 'fieldsSchema')) {
            $schema = $this->fieldsSchema();
            if ($schema) {
                $self = $this->createSchema($schema, $this);
            }
        }

        $data = static::anyToFrontend($self, $fields, $user, $scopes);
        $model = $self instanceof BaseSchema ? $self->model : $self;

        if ($user && $model instanceof Model) {
            $canView = $model->canView($user);
            if (is_array($canView)) {
                /** @var Model $modelClass */
                $modelClass = get_class($model);
                $notPermittedFields = array_diff(array_keys($modelClass::meta()), $canView);
                if ($notPermittedFields) {
                    $data = array_filter($data,
                        function ($attribute) use ($notPermittedFields) {
                            return !in_array($attribute, $notPermittedFields);
                        },
                        ARRAY_FILTER_USE_KEY
                    );
                }
            }
        }

        return $data;
    }
}
