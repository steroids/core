<?php

namespace steroids\core\structure;

use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\Request;

class RequestInfo extends BaseObject
{
    const METHOD_GET = 'get';
    const METHOD_POST = 'post';

    public string $url;
    public ?string $method = self::METHOD_GET;
    public ?array $params = [];
    public ?array $headers = [];
    public ?string $rawBody = null;

    /**
     * @param Request|null $request
     */
    public static function createFromYii($request = null)
    {
        $request = $request ?: \Yii::$app->request;

        $port = $request->port && $request->port !== 80 ? ':' . $request->port : '';
        return new static([
            'method' => $request->method,
            'url' => $request->hostInfo . $port . str_replace('?' . $request->queryString, '', $request->url),
            'params' => ArrayHelper::merge($request->get(), $request->post()),
            'headers' => $request->headers->toArray(),
            'rawBody' => $request->getRawBody(),
        ]);
    }

    /**
     * @param array|string $params
     * @return bool
     */
    public function hasParams($params)
    {
        if (!is_array($params)) {
            $params = [$params];
        }

        foreach ($params as $param) {
            if (!isset($this->params[$param])) {
                return false;
            }
        }
        return true;
    }

    public function toRaw()
    {
        $lines = [];
        $lines[] = strtoupper($this->method) . ' ' . (string)$this;
        foreach ($this->headers as $key => $value) {
            $lines[] = $key . ': ' . implode(';', (array)$value);
        }
        $lines[] = '';
        if ($this->rawBody) {
            $lines[] = $this->rawBody;
        } elseif ($this->method !== self::METHOD_GET && !empty($this->params)) {
            $lines[] = Json::encode($this->params);
        }
        return implode("\n", $lines);
    }

    public function __toString()
    {
        $link = new UrlInfo($this->url);
        if ($this->method === self::METHOD_GET) {
            $link->params = $this->params;
        }
        return (string)$link;
    }

}
