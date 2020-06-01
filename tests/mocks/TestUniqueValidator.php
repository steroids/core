<?php

namespace steroids\core\tests\mocks;

use yii\validators\SafeValidator;

class TestUniqueValidator extends SafeValidator
{
    public $targetClass;
    public $targetAttribute;
    public $filter;
    public $message;
    public $comboNotUnique;
    public $targetAttributeJunction = 'and';
    public $forceMasterDb =  true;
}