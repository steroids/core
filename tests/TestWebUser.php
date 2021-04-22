<?php

namespace steroids\core\tests;

use app\user\models\User;
use steroids\auth\models\AuthLogin;

/**
 *
 * @property bool $isGuest
 * @property mixed $identity
 * @property mixed $model
 */
class TestWebUser extends User
{
    public $isLogin = false;
    public $id;
    public $primaryKey;
    public $password;
    public $identityClass = 'app\user\models\User';
    public $accessToken;

    /**
     * @var AuthLogin
     */
    private $_login = false;


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


}