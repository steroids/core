<?php

namespace steroids\core\middleware;

use steroids\core\base\BaseSchema;
use steroids\core\base\FormModel;
use steroids\core\base\Model;
use steroids\core\base\SearchModel;
use yii\base\ActionEvent;
use yii\base\BaseObject;
use yii\data\BaseDataProvider;
use yii\web\Application;
use yii\web\Controller;
use yii\web\JsExpression;
use yii\web\Response;

class AjaxResponseMiddleware extends BaseObject
{
    /**
     * @param Application $app
     */
    public static function register($app)
    {
        if ($app instanceof Application) {
            $app->on(Controller::EVENT_AFTER_ACTION, [static::class, 'checkAjaxResponse']);
        }
    }

    /**
     * @param ActionEvent $event
     * @throws
     */
    public static function checkAjaxResponse($event)
    {
        $request = \Yii::$app->request;
        $response = \Yii::$app->response;

        $rawContentType = $request->contentType;
        if (($pos = strpos($rawContentType, ';')) !== false) {
            // e.g. application/json; charset=UTF-8
            $contentType = substr($rawContentType, 0, $pos);
        } else {
            $contentType = $rawContentType;
        }

        if ($response->format !== Response::FORMAT_RAW) {
            if (($contentType === 'application/json' && isset($request->parsers[$contentType]))
                || $response->format === Response::FORMAT_JSON
                || is_array($event->result)
                || $event->result instanceof \yii\base\Model
                || $event->result instanceof BaseDataProvider) {

                // Detect data provider
                if ($event->result instanceof SearchModel || $event->result instanceof BaseSchema) {
                    $data = Model::anyToFrontend($event->result, null, \Yii::$app->user->identity);
                } elseif ($event->result instanceof Model || $event->result instanceof FormModel) {
                    if ($event->result->hasErrors()) {
                        $errors = $event->result->getErrors();
                        $formName = $event->result->formName();
                        if ($formName) {
                            $errors = [$formName => $errors];
                        }

                        $data = ['errors' => $errors];
                    } else {
                        $data = Model::anyToFrontend($event->result,null, \Yii::$app->user->identity);
                    }
                } elseif ($event->result instanceof BaseDataProvider) {
                    $data = [
                        'meta' => null,
                        'items' => array_values($event->result->models),
                        'total' => $event->result->totalCount,
                    ];
                } elseif (is_array($event->result)) {
                    $data = Model::anyToFrontend($event->result, null, \Yii::$app->user->identity);
                } else {
                    $data = [];
                }

                // Ajax redirect
                $location = $response->headers->get('Location')
                    ?: $response->headers->get('X-Pjax-Url')
                        ?: $response->headers->get('X-Redirect');
                if ($location) {
                    $data['redirectUrl'] = $location;
                    $response->headers->remove('Location');
                    $response->statusCode = 200;
                } else {
                    // Flashes
                    $session = \Yii::$app->session;
                    $flashes = $session->getAllFlashes(true);
                    if (!empty($flashes)) {
                        $data['flashes'] = $flashes;
                    }
                }

                $event->result = is_array($data) && empty($data) ? new JsExpression('[]') : $data;
            }
        }

        if (is_array($event->result)) {
            $response->format = Response::FORMAT_JSON;
        }
    }
}
