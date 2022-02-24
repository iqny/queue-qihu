<?php

namespace Qihu\Queue\Drive;
use Qihu\Queue\Drive\Rocketmq\Rocketmq;
use Qihu\Queue\Drive\DriveInterface;
class RocketmqFactory implements Factory{

    public static function createClient($cfg): DriveInterface
    {
        return new Rocketmq($cfg);
    }
}
