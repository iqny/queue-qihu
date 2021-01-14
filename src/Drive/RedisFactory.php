<?php

namespace Qihu\Queue\Drive;
use Qihu\Queue\Drive\Redis\Redis;

class RedisFactory implements Factory{

    public static function createClient($cfg): Redis
    {
        return new Redis($cfg);
    }
}
