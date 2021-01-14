<?php

namespace Qihu\Queue\Drive;

use Qihu\Queue\Drive\Rabbitmq\Rabbitmq;

class RabbitmqFactory implements Factory
{

    public static function createClient($cfg): Rabbitmq
    {
        return new Rabbitmq($cfg);
        // TODO: Implement createClient() method.
    }
}
