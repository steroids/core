<?php

namespace steroids\core\base;

use Yii;
use steroids\core\components\Types;
use steroids\core\components\AuthManager;
use steroids\core\components\SiteMap;
use yii\web\Application;
use yii\web\UrlManager;

/**
 * @property-read AuthManager $authManager
 * @property-read SiteMap $siteMap
 * @property-read Types $types
 * @property-read UrlManager $urlManager
 */
class WebApplication extends Application
{
    /**
     * @inheritdoc
     */
    protected function bootstrap()
    {
        $versionFilePath = STEROIDS_ROOT_DIR . '/public/version.txt';
        if (file_exists($versionFilePath)) {
            $this->version = trim(file_get_contents($versionFilePath));
        }

        //Yii::setAlias('@bower', Yii::getAlias('@vendor') . '/bower-asset');

        parent::bootstrap();
    }

    public function coreComponents()
    {
        $components = parent::coreComponents();
        unset($components['mailer']);
        return $components;
    }
}
