<?php

namespace Qihu\Queue;

use Qihu\Queue\Drive\RabbitmqFactory;
use Qihu\Queue\Drive\RedisFactory;
use Qihu\Queue\Drive\RocketmqFactory;

class ConnPool
{
    private static $pool = [];

    /**
     * @param string $queueName
     * @return mixed|Drive\DriveInterface
     */
    public static function getQueueClient(string $queueName = '')
    {
        if (isset(self::$pool[$queueName])) {
            return self::$pool[$queueName];
        }
        $cfg = config('queueqihu');
        $drive = $cfg['connect']['drive'];
        $drive = isset($cfg['queue'][$queueName]['drive']) && !empty($cfg['queue'][$queueName]['drive']) ? $cfg['queue'][$queueName]['drive'] : $drive;
        switch ($drive) {
            case 'rabbitmq':
                self::$pool[$queueName] = RabbitmqFactory::createClient($cfg[$drive]);
                break;
            case 'rocketmq':
                self::$pool[$queueName] = RocketmqFactory::createClient($cfg[$drive]);
                break;
            default:
                self::$pool[$queueName] = RedisFactory::createClient($cfg[$drive]);
                break;
        }
        return self::$pool[$queueName];
    }
}
