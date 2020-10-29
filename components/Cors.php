<?php

namespace steroids\core\components;

use steroids\core\base\WebApplication;
use yii\base\BootstrapInterface;
use yii\base\Component;

class Cors extends Component implements BootstrapInterface
{
    /**
     * @var array set '*'
     * to allow all domains
     */
    public array $allowDomains = [];

    /**
     * @var array
     * set '*' to allow all headers
     */
    public array $allowHeaders = [
        'Origin',
        'X-Requested-With',
        'Content-Type',
        'Accept',
        'Authorization',
        'X-CSRF-Token',

        // For file PUT upload
        'If-None-Match',
        'If-Modified-Since',
        'Cache-Control',
        'X-Requested-With',
        'Content-Disposition',
        'Content-Range',
    ];

    /**
     * @var string[]
     */
    public array $allowMethods = ['POST', 'PUT', 'GET', 'OPTIONS', 'DELETE'];

    /**
     * @var bool
     */
    public bool $allowCredentials = true;
    public int $maxAge = 86400;
    public array $exposeHeaders = [];

    public function bootstrap($app)
    {
        if ($this->allowDomains && $app instanceof WebApplication) {
            $origin = [];
            foreach ($this->allowDomains as $domain) {
                if (strpos('://', $domain) === false) {
                    $origin[] = 'https://' . $domain;
                    $origin[] = 'http://' . $domain;
                } else {
                    $origin[] = $domain;
                }
            }

            $cors = new \yii\filters\Cors([
                'cors' => [
                    'Origin' => $origin,
                    'Access-Control-Request-Method' => $this->allowMethods,
                    'Access-Control-Request-Headers' => $this->allowHeaders,
                    'Access-Control-Allow-Credentials' => $this->allowCredentials,
                    'Access-Control-Max-Age' => $this->maxAge,
                    'Access-Control-Expose-Headers' => $this->exposeHeaders,
                ],
                'request' => \Yii::$app->getRequest(),
                'response' => \Yii::$app->getResponse(),
            ]);

            $requestCorsHeaders = $cors->extractHeaders();
            $responseCorsHeaders = $cors->prepareHeaders($requestCorsHeaders);
            $cors->addCorsHeaders($cors->response, $responseCorsHeaders);

            if ($cors->request->isOptions && $cors->request->headers->has('Access-Control-Request-Method')) {
                // it is CORS preflight request, respond with 200 OK without further processing
                $cors->response->setStatusCode(200);
                \Yii::$app->end();
            }
        }
    }
}
