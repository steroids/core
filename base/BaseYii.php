<?php

namespace steroids\core\base;

require(STEROIDS_VENDOR_DIR . '/yiisoft/yii2/BaseYii.php');

class BaseYii extends \yii\BaseYii
{
}

spl_autoload_register(['steroids\core\base\BaseYii', 'autoload'], true, true);
BaseYii::$classMap = require(STEROIDS_VENDOR_DIR . '/yiisoft/yii2/classes.php');
BaseYii::$container = new \yii\di\Container();
