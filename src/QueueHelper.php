<?php

namespace Qihu\Queue;

use Qihu\Queue\Drive\RabbitmqFactory;
use Qihu\Queue\Drive\RedisFactory;

class QueueHelper
{
    protected static $queueConnect = null;

    /**
     *
     * @return Drive\Rabbitmq\Rabbitmq|Drive\Redis\Redis
     */
    public static function getQueueClient()
    {
        if (self::$queueConnect) {
            return self::$queueConnect;
        }
        $cfg = config('queueqihu');
        $drive = $cfg['connect']['drive'];
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
