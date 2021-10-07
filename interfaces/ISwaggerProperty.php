<?php

namespace steroids\core\interfaces;

interface ISwaggerProperty
{
    public function setPhpType(string $value);
    public function setFormat(string $value);
    public function setIsArray(bool $value);
    public function setEnum(array $keys);
}