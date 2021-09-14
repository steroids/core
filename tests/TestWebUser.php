<?php

namespace steroids\core\tests;

use app\user\models\User;
use steroids\auth\components\BearerWebUser;
use steroids\auth\models\AuthLogin;
use yii\web\IdentityInterface;

/**
 *
 * @property bool $isGuest
 * @property mixed $identity
 * @property mixed $model
 */
class TestWebUser extends BearerWebUser
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
        return !$this->isLogin;
    }

    public function login(IdentityInterface $identity, $duration = 0)
    {
        $this->isLogin = true;
        $this->accessToken = 'testtoken';
        return true;
    }

    public function logout($destroySession = true)
    {
        $this->isLogin = false;
        $this->accessToken = null;
        return true;
    }

    public function getIdentity($autoRenew = true)
    {
        return User::findOne(['id' => $this->id]);
    }

    public function getModel()
    {
        return User::findOne(['id' => $this->id]);
    }


}