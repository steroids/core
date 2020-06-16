<?php

namespace steroids\core\tests\mocks;

use app\user\models\User;
use yii\base\BaseObject;

class TestWebUser extends BaseObject
{
    const DEFAULT_ROLE = 'user';

    public $isLogin = false;
    public $id;
    public $password;
    public $identityClass = 'app\user\models\User';
    public $accessToken;

    public function getIsGuest()
    {
        return $this->isLogin;
    }

    public function login()
    {
        $this->isLogin = true;
        return true;
    }

    public function logout()
    {
        $this->isLogin = false;
        return true;
    }

    public function getIdentity()
    {
        return User::findOne(['id' => $this->id]);
    }

    public function getModel()
    {
        return User::findOne(['id' => $this->id]);
    }

    public function getDefaultRole()
    {
        return static::DEFAULT_ROLE;
    }
}
