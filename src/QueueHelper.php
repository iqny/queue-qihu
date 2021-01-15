<?php

namespace Qihu\Queue;

use Qihu\Queue\Drive\RabbitmqFactory;
use Qihu\Queue\Drive\RedisFactory;

class QueueHelper
{
    protected static $queueConnect = null;

    /**
     * @param string $queueName
     * @return Drive\Rabbitmq\Rabbitmq|Drive\Redis\Redis|null
     */
    public static function getQueueClient($queueName = '')
    {
        if (self::$queueConnect) {
            return self::$queueConnect;
        }
        $cfg = config('queueqihu');
        $drive = $cfg['connect']['drive'];
        $drive = isset($cfg['queue'][$queueName]['drive']) && !empty($cfg['queue'][$queueName]['drive']) ? $cfg['queue'][$queueName]['drive'] : $drive;
        switch ($drive) {
            case 'rabbitmq':
                self::$queueConnect = RabbitmqFactory::createClient($cfg[$drive]);
                break;
            default:
                self::$queueConnect = RedisFactory::createClient($cfg[$drive]);
                break;
        }
        return self::$queueConnect;
    }
}
