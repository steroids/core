<?php

namespace steroids\core\components;

use steroids\auth\UserInterface;
use yii\base\Component;
use yii\web\IdentityInterface;

/**
 * @property-read bool $isGuest
 * @property-read int|null $id
 * @property-read UserInterface|null $identity
 * @property-read UserInterface|null $model
 * @property-read string|null $accessToken
 */
class BlankUser extends \yii\web\User
{

    public function init()
    {
        Component::init();
    }

    /**
     * @return bool
     */
    public function getIsGuest()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function getIdentity($autoRenew = true)
    {
        return null;
    }

    public function setIdentity($value)
    {
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return null;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function login(IdentityInterface $identity, $duration = 0)
    {
    }

    /**
     * @inheritdoc
     */
    public function switchIdentity($user, $duration = 0)
    {
    }

    /**
     * @inheritdoc
     */
    public function logout($destroySession = true)
    {
    }
}
