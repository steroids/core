<?php

namespace steroids\core\base;

use yii\console\Application;

class ConsoleApplication extends Application
{
    public function coreComponents()
    {
        $components = parent::coreComponents();
        unset($components['mailer']);
        return $components;
    }
}
