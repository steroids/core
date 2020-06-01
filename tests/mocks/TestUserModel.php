<?php

namespace steroids\core\tests\mocks;

use steroids\auth\UserInterface;
use yii\base\Model;

class TestUserModel extends Model implements UserInterface
{
    public $id;
    public $email;
    public $phone;
    public $passwordHash;

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        return new static(['id' => $id]);
    }

    /**
     * @inheritdoc
     */
    public static function findBy($login, $attributes)
    {
        return new static();
    }

    /**
     * @inheritdoc
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    public function setAttribute($key, $value)
    {
        $this->$key = $value;
    }

    public function getAttribute($key)
    {
        return $this->$key;
    }

    public function save()
    {
        $this->id = time();
        return true;
    }

    public function sendNotify($templateName, $params = [])
    {
        // TODO
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return false;
    }

    /**
     * @param string $password
     * @return boolean
     */
    public function validatePassword($password)
    {
        return true;
    }
}