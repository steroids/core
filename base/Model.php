<?php

namespace steroids\core\base;

use steroids\auth\UserInterface;
use steroids\core\traits\RelationSaveTrait;
use steroids\core\components\AuthManager;
use steroids\core\exceptions\ModelDeleteException;
use steroids\core\exceptions\ModelSaveException;
use steroids\core\traits\MetaTrait;
use steroids\core\traits\SecurityTrait;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;
use yii\web\NotFoundHttpException;

/**
 * @property-read string $modelLabel
 */
class Model extends ActiveRecord
{
    const SCOPE_PREFIX = '_scope_';
    const SCOPE_DEFAULT = self::SCOPE_PREFIX . 'default';
    const SCOPE_LIST = self::SCOPE_PREFIX . 'list';
    const SCOPE_FORM = self::SCOPE_PREFIX . 'form';
    const SCOPE_DETAIL = self::SCOPE_PREFIX . 'detail';
    const SCOPE_DICTIONARY = self::SCOPE_PREFIX . 'dictionary';
    const SCOPE_ADMIN = self::SCOPE_PREFIX . 'admin';

    use MetaTrait;
    use RelationSaveTrait;
    use SecurityTrait;

    protected static $_cans;

    /**
     * @return string
     */
    public static function getRequestParamName()
    {
        try {
            $pk = static::primaryKey()[0];
        } catch (InvalidConfigException $e) {
            $pk = 'id';
        }
        return lcfirst(substr(strrchr(static::className(), "\\"), 1)) . ucfirst($pk);
    }

    /**
     * @param array $condition
     * @param array $additionalFields
     * @param bool $onlyVisible
     * @return array
     * @throws InvalidConfigException
     */
    public static function asEnum($condition = [], $additionalFields = [], $onlyVisible = true)
    {
        $query = static::find()
            ->andFilterWhere($condition)
            ->limit(1000);

        if ($onlyVisible && in_array('isVisible', static::getTableSchema()->columns)) {
            $query->andWhere(['isVisible' => true]);
        }

        $result = [];

        $models = $query->all();
        if (!count($models)) {
            return $result;
        }

        $idKey = static::getEnumIdKey($models[0]);
        $labelKey = static::getEnumLabelKey($models[0]);

        foreach ($query->all() as $model) {
            $item = $model->toFrontend(array_merge(
                [
                    'id' => $idKey,
                    'label' => $labelKey,
                ],
                $additionalFields
            ));
            $result[$item['id']] = $item;
        }

        ArrayHelper::multisort($result, 'label');
        return array_values($result);
    }

    protected static function getEnumIdKey(ActiveRecord $model)
    {
        return $model::primaryKey()[0];
    }

    protected static function getEnumLabelKey(ActiveRecord $model)
    {
        foreach (['title', 'label', 'name'] as $attribute) {
            if ($model->canGetProperty($attribute)) {
                return $attribute;
            }
        }

        return $model::primaryKey()[0];
    }

    public function frontendFields(UserInterface $user = null)
    {
        return [
            self::SCOPE_DEFAULT => $this->fields(),
        ];
    }

    public function getPermissions($user)
    {

        if (static::$_cans === null) {
            static::$_cans = [];
            $info = new \ReflectionClass(get_class($this));
            foreach ($info->getMethods() as $method) {
                $parameters = $method->getParameters();
                if (count($parameters) === 0 || $parameters[0]->getName() !== 'user') {
                    continue;
                }

                $name = $method->getName();
                if (preg_match('/^can((?!Attribute)\w)*$/', $name)) {
                    static::$_cans[] = $name;
                }
            }
        }

        $result = [];
        foreach (static::$_cans as $can) {
            $result[$can] = $this->$can($user) ?: false;
        }

        return $result;
    }

    /**
     * @return string
     */
    public function getModelLabel()
    {
        foreach (['title', 'label', 'name'] as $attribute) {
            $label = $this->getAttribute($attribute);
            if ($label) {
                return $label;
            }
        }
        return '#' . $this->primaryKey;
    }

    /**
     * @inheritdoc
     */
    public function transactions()
    {
        return [
            self::SCENARIO_DEFAULT => self::OP_ALL,
        ];
    }

    /**
     * @param string|array $condition
     * @return static
     * @throws NotFoundHttpException
     */
    public static function findOrPanic($condition)
    {
        $model = static::findOne($condition);
        if (!$model) {
            throw new NotFoundHttpException('Запись не найдена' . (YII_DEBUG ? ' - ' . static::class : ''));
        }
        return $model;
    }

    /**
     * @param string[]|null $attributeNames
     * @throws ModelSaveException
     */
    public function saveOrPanic($attributeNames = null)
    {
        if (!$this->save(true, $attributeNames)) {
            throw new ModelSaveException($this);
        }
    }

    /**
     * @throws ModelDeleteException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function deleteOrPanic()
    {
        if (!$this->delete()) {
            throw new ModelDeleteException();
        }
    }

    /**
     * @inheritDoc
     */
    public function formName()
    {
        // Disable form name by default, because our backend only for REST API
        // and form widgets not used
        return '';
    }

    /**
     * @inheritdoc
     */
    public function load($data, $formName = null)
    {
        $formName = $formName === null ? $this->formName() : $formName;

        // Load relations
        $this->loadRelationData($data, $formName);
        $this->loadRelationIds($data, $formName);

        return parent::load($data, $formName);
    }

    public function validate($attributeNames = null, $clearErrors = true)
    {
        if ($clearErrors) {
            $this->clearErrors();
        }

        if (!$this->beforeValidate()) {
            return false;
        }

        // Validate relations
        $this->validateRelationData();

        $scenarios = $this->scenarios();
        $scenario = $this->getScenario();
        if (!isset($scenarios[$scenario])) {
            throw new InvalidConfigException("Unknown scenario: $scenario");
        }

        if ($attributeNames === null) {
            $attributeNames = $this->activeAttributes();
        }

        foreach ($this->getActiveValidators() as $validator) {
            $validator->validateAttributes($this, $attributeNames);
        }
        $this->afterValidate();

        return !$this->hasErrors();
    }

    public function save($runValidation = true, $attributeNames = null)
    {
        // Validate
        if ($runValidation && !$this->validate($attributeNames)) {
            return false;
        }

        $transaction = static::getDb()->beginTransaction();
        try {
            $this->saveRelationDataBeforeSelf();

            // Save self without validation
            if (!parent::save(false, $attributeNames)) {
                $transaction->rollBack();
                return false;
            }

            $this->saveRelationIds();
            $this->saveRelationDataAfterSelf();

            $transaction->commit();
        } catch (\Exception $e) {
            $transaction->rollBack();
            throw $e;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    public function hasErrors($attribute = null)
    {
        return !empty($this->getErrors($attribute));
    }

    /**
     * @inheritdoc
     */
    public function getErrors($attribute = null)
    {
        $errors = parent::getErrors($attribute);

        if ($attribute === null && !empty($this->_listenRelations)) {
            $errors = array_merge($errors, $this->getRelationErrors());
        }

        return $errors;
    }

    /**
     * @param UserInterface|IdentityInterface|Model $user
     * @return bool|array
     */
    public function canView($user)
    {
        if (\Yii::$app->has('authManager') && \Yii::$app->authManager->enableForModel) {
            return $this->getPermittedAttributes($user, AuthManager::RULE_MODEL_VIEW);
        }

        return true;
    }

    /**
     * @param UserInterface|IdentityInterface|Model $user
     * @return bool|array
     */
    public function canCreate($user)
    {
        if (\Yii::$app->has('authManager') && \Yii::$app->authManager->enableForModel) {
            return $this->getPermittedAttributes($user, AuthManager::RULE_MODEL_CREATE);
        }

        return true;
    }

    /**
     * @param UserInterface|IdentityInterface|Model $user
     * @return bool|array
     */
    public function canUpdate($user)
    {
        if (\Yii::$app->has('authManager') && \Yii::$app->authManager->enableForModel) {
            return $this->canUpdated()
                ? $this->getPermittedAttributes($user, AuthManager::RULE_MODEL_UPDATE)
                : false;
        }

        return $this->canUpdated();
    }

    /**
     * @param UserInterface|IdentityInterface|Model $user
     * @return bool
     */
    public function canDelete($user)
    {
        if (\Yii::$app->has('authManager') && \Yii::$app->authManager->enableForModel) {
            return \Yii::$app->authManager->checkModelAccess($user, $this, AuthManager::RULE_MODEL_DELETE) && $this->canDeleted();
        }
        return $this->canDeleted();
    }

    /**
     * @return bool
     */
    public function canUpdated()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function canDeleted()
    {
        return $this->canUpdated() && !$this->isNewRecord;
    }

    /**
     * @param UserInterface|IdentityInterface|Model $user
     * @param string $attributeName
     * @return bool
     */
    public function canCreateAttribute($user, $attributeName)
    {
        if (\Yii::$app->has('authManager') && \Yii::$app->authManager->enableForModel) {
            return \Yii::$app->authManager->checkAttributeAccess(
                $user,
                $this,
                $attributeName,
                AuthManager::RULE_MODEL_CREATE
            );
        }
        return true;
    }

    /**
     * @param UserInterface|IdentityInterface|Model $user
     * @param string $attributeName
     * @return bool
     */
    public function canUpdateAttribute($user, $attributeName)
    {
        if (\Yii::$app->has('authManager') && \Yii::$app->authManager->enableForModel) {
            return \Yii::$app->authManager->checkAttributeAccess(
                $user,
                $this,
                $attributeName,
                AuthManager::RULE_MODEL_UPDATE
            );
        }
        return true;
    }

    /**
     * @param UserInterface|IdentityInterface|Model $user
     * @param string $attributeName
     * @return bool
     */
    public function canViewAttribute($user, $attributeName)
    {
        if (\Yii::$app->has('authManager') && \Yii::$app->authManager->enableForModel) {
            return \Yii::$app->authManager->checkAttributeAccess(
                $user,
                $this,
                $attributeName,
                AuthManager::RULE_MODEL_VIEW
            );
        }
        return true;
    }

    /**
     * @param UserInterface|IdentityInterface|Model $user
     * @param string $rule
     * @return array|bool of attribute names
     */
    protected function getPermittedAttributes($user, $rule)
    {
        $permissionCheckMethod = 'can' . ucfirst($rule) . 'Attribute';

        $attributes = array_values(
            array_filter(
                array_keys(static::meta()),
                function ($attribute) use ($user, $permissionCheckMethod) {
                    return $this->$permissionCheckMethod($user, $attribute);
                }
            )
        );

        if (count($attributes) === 0 && \Yii::$app->authManager->checkModelAccess($user, $this, $rule)) {
            return true;
        }
        return $attributes;
    }

    public function beforeSave($insert)
    {
        return parent::beforeSave($insert) && $this->canUpdated();
    }

    public function beforeDelete()
    {
        return parent::beforeDelete() && $this->canDeleted();
    }
}
