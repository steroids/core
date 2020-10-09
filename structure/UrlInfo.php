<?php

namespace steroids\core\structure;

use yii\base\BaseObject;
use yii\base\Exception;

/**
 * Модель для парсинга и формирования ссылки
 *
 * @author affka
 */
class UrlInfo extends BaseObject
{
    const PROTOCOL_HTTPS = 'https';
    const PROTOCOL_HTTP = 'http';
    const URL_REGEXP = '/((https?:\/\/www\.)|(https?:\/\/)|(www\.))([^\/\n\r\t\"\' ]+)([^\?\n\r\t\"\'\# ]*)\??([^\n\r\t\"\'\# ]*)\#?([^\n\r\t\"\' ]*)/iu';

    public string $protocol;
    public string $source;
    public string $domain;
    public string $host;
    public ?string $path = '';
    public ?array $params = [];
    public ?string $hash = '';
    public ?array $hashParams = [];

    public function __construct($config = '')
    {
        if (is_string($config)) {
            preg_match(static::URL_REGEXP, $config, $match);

            if (count($match) > 5) {
                $this->protocol = strpos($match[1], 'https') !== false ? self::PROTOCOL_HTTPS : self::PROTOCOL_HTTP;
                $this->source = $config;
                $this->domain = (string)preg_replace('/^(.+\.)?([^\.]+\.[^\.]+)$/iu', '\\2', $match[5]);
                $this->host = $match[5];
                $this->path = $match[6];
                $this->params = $this->stringToParameters($match[7]);
                $this->hash = $match[8];
                $this->hashParams = $this->stringToParameters($match[8]);
            } else {
                throw new Exception('Wrong url: ' . $config);
            }

            $config = [];
        }

        parent::__construct($config);
    }

    public function hasParam($name)
    {
        return array_key_exists($name, $this->params) !== false;
    }

    public function getParam($name)
    {
        return $this->hasParam($name) ? $this->params[$name] : null;
    }

    public function setParam($name, $value)
    {
        $this->params[$name] = $value;
    }

    public function setParams(array $params)
    {
        $this->params = array_merge($this->params, $params);
    }

    public function hasHashParam($name)
    {
        return array_key_exists($name, $this->hashParams);
    }

    public function getHashParam($name)
    {
        return $this->hasHashParam($name) ? $this->hashParams[$name] : null;
    }

    public function setHashParam($name, $value)
    {
        $this->hashParams[$name] = $value;
    }

    public function setHashParams(array $params)
    {
        $this->hashParams = array_merge($this->hashParams, $params);
    }

    public function __toString()
    {
        $link = $this->protocol . '://';
        $link .= $this->host;
        $link .= $this->path;

        $stringParameters = $this->parametersToString($this->params);
        if ($stringParameters) {
            $link .= '?' . $stringParameters;
        }

        return $link;
    }

    private function stringToParameters($parametersString)
    {
        $parameters = array();
        foreach (explode('&', $parametersString) as $paramString) {
            $paramArr = explode('=', $paramString);
            if (count($paramArr) === 2) {
                $parameters[$paramArr[0]] = $paramArr[1];
            }
        }
        return $parameters;
    }

    private function parametersToString($parameters)
    {
        return http_build_query($parameters);
    }
}
