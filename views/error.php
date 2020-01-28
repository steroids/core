<?php

namespace steroids\views;

use Exception;
use yii\web\ErrorHandler;
use yii\web\HttpException;

/* @var $exception HttpException|Exception */
/* @var $handler ErrorHandler */

$code = $exception instanceof \yii\web\HttpException
    ? $exception->statusCode
    : $exception->getCode();
$message = $exception instanceof \yii\base\UserException
    ? $exception->getMessage()
    : 'An internal server error occurred.';

echo '#' . $code . ' ' . $message;
