<?php

namespace Qihu\Queue\Drive;
use Qihu\Queue\Drive\Redis\Redis;

class RedisFactory implements Factory{

    public static function createClient($cfg): DriveInterface
    {
        return new Redis($cfg);
    }
}
