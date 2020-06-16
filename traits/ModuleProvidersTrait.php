<?php

namespace steroids\core\traits;

use steroids\auth\providers\BaseAuthProvider;
use steroids\notifier\providers\BaseNotifierProvider;
use yii\helpers\ArrayHelper;

trait ModuleProvidersTrait {

    /**
     * @var BaseAuthProvider[]|array
     */
    public array $providers;

    //public array $providersClasses;

    /**
     * @param string $name
     * @return BaseNotifierProvider|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getProvider($name)
    {
        if (!$this->providers || !isset($this->providers[$name])) {
            return null;
        }
        if (is_array($this->providers[$name])) {
            $this->providers[$name] = \Yii::createObject(array_merge(
                ['class' => ArrayHelper::getValue($this->providersClasses, $name)],
                $this->providers[$name],
                ['name' => $name]
            ));
        }
        return $this->providers[$name];
    }

}
