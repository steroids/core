<?php

namespace steroids\core\components;

use Yii;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use yii\web\UrlRule;

/**
 * @package steroids\core\components
 * @property callable|callable[]|null $accessCheck
 * @property-read string|array $normalizedUrl
 * @property-read array $pathIds
 */
class SiteMapItem extends BaseObject
{
    /**
     * @var int|string
     */
    public $id;

    /**
     * @var string
     */
    public $label;

    /**
     * @var string|array
     */
    public $url;

    /**
     * Value format is identical to item from \yii\web\UrlManager::rules
     * @var string|array|UrlRule
     */
    public $urlRule;

    /**
     * @var float
     */
    public $order = 0;

    /**
     * @var SiteMapItem[]
     */
    public $items = [];

    /**
     * @var SiteMap
     */
    public $owner;

    /**
     * @var SiteMapItem
     */
    public $parent;

    /**
     * @var bool|string|int
     */
    public $redirectToChild = false;

    /**
     * @var callable|callable[]
     */
    private $_accessCheck;

    /**
     * @var string
     */
    public $controllerRoute;

    /**
     * @return callable|callable[]|null
     */
    public function getAccessCheck()
    {
        if ($this->_accessCheck === null && $this->parent) {
            return $this->parent->getAccessCheck();
        }
        return $this->_accessCheck;
    }

    /**
     * @param callable|callable[]|null $value
     */
    public function setAccessCheck($value)
    {
        $this->_accessCheck = $value;
    }

    /**
     * @return array
     */
    public function getPathIds()
    {
        return array_merge(ArrayHelper::getValue($this->parent, 'pathIds', []), [$this->id]);
    }

    /**
     * @param array $url
     * @return bool|mixed
     */
    public function checkVisible($url)
    {
        if (is_callable($this->accessCheck)) {
            return call_user_func($this->accessCheck, $url);
        }

        if (Yii::$app && Yii::$app->has('authManager') && Yii::$app->authManager instanceof AuthManager) {
            return Yii::$app->authManager->checkMenuAccess(Yii::$app->user->identity, $this);
        }

        return false;
    }

    public function getNormalizedUrl()
    {
        if (is_array($this->url)) {
            $url = [$this->url[0]];

            foreach ($this->url as $key => $value) {
                if ($value !== null) {
                    $url[$key] = $value;
                }
            }

            // Append keys from url rule
            if (is_string($this->urlRule)) {
                preg_match_all('/<([^:>]+)[:>]/', $this->urlRule, $matches);
                foreach ($matches[1] as $key) {
                    if (!isset($url[$key])) {
                        $url[$key] = SiteMap::paramGet($key);
                    }
                }
            }

            // Normalize route
            if ($url[0] && strpos($url[0], '/') === false) {
                $url[0] = $this->controllerRoute . '/' . $url[0];
            }

            return $url;
        }
        return $this->url;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'label' => $this->label,
            'url' => $this->getNormalizedUrl(),
            'items' => $this->items,
        ];
    }
}
