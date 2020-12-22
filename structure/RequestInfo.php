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

    public static function createFromUrl(string $url)
    {
        $info = new UrlInfo($url);
        return new static([
            'url' => $info->protocol . '://' . $info->host . $info->path,
            'params' => $info->params,
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

    /**
     * @param string $key
     * @return mixed|null
     * @throws \Exception
     */
    public function getParam(string $key)
    {
        return ArrayHelper::getValue($this->params, $key);
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getHeader(string $name)
    {
        foreach ($this->headers as $key => $value) {
            if (strtolower($name) === strtolower($key)) {
                return is_array($value) ? array_values($value)[0] : $value;
            }
        }
        return null;
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

    public static function createFromRaw($raw)
    {
        $request = new static();

        $index = strpos($raw, "\n\n");

        // Head
        $head = substr($raw, 0, $index);
        foreach(preg_split("/\n/", $head) as $lineIndex => $line){
            if ($lineIndex === 0) {
                list($method, $url) = explode(' ', $line);
                $request->method = strtolower($method);
                $request->url = $url;
            } else {
                $headerIndex = strpos($line, ':');
                if ($headerIndex !== false) {
                    $request->headers[substr($line, 0, $headerIndex)] = trim(substr($line, $headerIndex + 1));
                }
            }
        }

        // Body
        $body = trim(substr($raw, $index));
        $request->rawBody = $body;
        if (in_array(substr($body, 0, 1), ['{', '['])) {
            // Json
            $request->params = Json::decode($body);
        } else {
            // Query string
            parse_str($body, $request->params);
        }

        return $request;
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
