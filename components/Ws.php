<?php

namespace steroids\core\components;

use yii\base\Component;
use yii\helpers\Json;
use steroids\auth\UserInterface;

class Ws extends Component
{
    const REDIS_KEY_WS_TOKENS = 'tokens';
    const REDIS_EVENT_TOKENS_UPDATE = 'tokens_update';

    /**
     * @var bool
     */
    public bool $enable = true;

    /**
     * @var string
     */
    public string $url = 'ws://localhost:1432';

    /**
     * @var string
     */
    public string $redisNamespace = 'app:';

    /**
     * @param string|array $stream
     * @param string $event
     * @param array $data
     */
    public function push($stream, $event, $data)
    {
        if (!$this->enable) {
            return;
        }

        $name = is_array($stream) ? $stream[0] : $stream;
        \Yii::$app->redis->publish($this->redisNamespace . $name, Json::encode([
            'id' => is_array($stream) ? $stream[1] : null,
            'stream' => $name,
            'event' => $event,
            'data' => $data,
        ]));

        if (STEROIDS_IS_CLI) {
            echo date('Y-m-d H:i:s') . " Push to WS, stream: $name, event: $event, data: " . Json::encode($data) . "\n";
        }
    }

    /**
     * @param UserInterface $user
     * @param string $token
     */
    public function addToken($user, $token)
    {
        if (!$this->enable) {
            return;
        }

        if (method_exists($user, 'getWsStreams')) {
            $key = $this->redisNamespace . self::REDIS_KEY_WS_TOKENS . ':' . $token;
            \Yii::$app->redis->set($key, Json::encode($user->getWsStreams()));
            \Yii::$app->redis->expire($key, 24 * 3600);
        }
    }

    /**
     * @param UserInterface $user
     * @param array $newStreams
     */
    /*public function addStreams($user, array $newStreams)
    {
        if (!$this->enable) {
            return;
        }

        $tokens = $user->getLogins()->select('wsToken')->column();
        foreach ($tokens as $token) {
            if (!$token) {
                continue;
            }

            $key = $this->redisNamespace . self::REDIS_KEY_WS_TOKENS . ':' . $token;
            $streamsJson = \Yii::$app->redis->get($key);
            if (!$streamsJson) {
                continue;
            }

            $streams = Json::decode($streamsJson);
            foreach ($newStreams as $newStream) {
                if (is_array($newStream)) {
                    $isFind = false;
                    foreach ($streams as $i => $stream) {
                        if (is_array($stream) && $newStream[0] === $stream[0]) {
                            $streams[$i][1] = array_unique(array_merge((array)$streams[$i][1], (array)$newStream[1]));
                            $isFind = true;
                            break;
                        }
                    }
                    if (!$isFind) {
                        $streams[] = $newStream;
                    }
                } elseif (!in_array($newStream, $streams)) {
                    $streams[] = $newStream;
                }
            }
            \Yii::$app->redis->set($key, Json::encode($streams));
        }

        \Yii::$app->redis->publish($this->redisNamespace . self::REDIS_EVENT_TOKENS_UPDATE, Json::encode($tokens));
    }*/

}
