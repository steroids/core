<?php

namespace steroids\core\tests\traits;

use steroids\core\base\FormModel;
use steroids\core\base\Model;
use steroids\core\tests\TestWebUser;
use yii\web\Request;
use Yii;

trait ApiCallTrait
{

    /**
     * @param $method
     * @param null $userId
     * @param array $params
     * @return array|int|mixed|\yii\console\Response
     * @throws Yii\base\InvalidConfigException
     * @throws \yii\base\InvalidRouteException
     * @throws \yii\console\Exception
     */
    public function callApi($method, $userId = null, $params = [])
    {
        Yii::$app->set('user', new TestWebUser(['id' => $userId, 'primaryKey' => $userId]));

        $parts = explode(' ', $method);
        $httpMethod = count($parts) > 1 ? strtoupper($parts[0]) : 'GET';
        $method = end($parts);

        $url = ($method ? '/' . ltrim($method, '/') : '');
        $request = new Request([
            'pathInfo' => $url,
        ]);
        $request->headers->add('X-Http-Method-Override', $httpMethod);
//        var_dump(Yii::$app);die;
        if ($httpMethod === 'GET') {
            $request->setQueryParams($params);
            Yii::$app->request->setQueryParams($params);
            Yii::$app->request->setBodyParams([]);
        } else {
            Yii::$app->request->setQueryParams([]);
            Yii::$app->request->setBodyParams($params);
        }

        list($route, $routeParams) = Yii::$app->urlManager->parseRequest($request);

        $response = Yii::$app->runAction($route, array_merge($params, $routeParams));

        if (is_object($response)) {
            if ($response instanceof FormModel) {
                return $this->renderFormModel($response, '');
            }
            if ($response instanceof Model) {
                return array_merge($response->toFrontend(), ['errors' => $response->errors]);
            }
        }
        return $response;
    }

    /**
     * @param \steroids\core\base\Model|\steroids\core\base\FormModel $model
     * @param null $formName
     * @return array
     * @throws yii\base\InvalidConfigException
     */
    public function renderFormModel($model, $formName = null)
    {
        $result = [];
        if ($model->hasErrors()) {
            $errors = $model->getErrors();

            // Apply form name
            $formName = $formName !== null ? $formName : $model->formName();
            if ($formName) {
                $errors = [$formName => $errors];
            }

            $result['errors'] = $errors;
        }

        if ($model->isSecurityRequired()) {
            $result['security'] = $model->getSecurityComponent();
        }
        return array_merge($result, $model->toFrontend());
    }
}
