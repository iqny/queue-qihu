<?php

namespace Qihu\Queue\Facades;

use Illuminate\Support\Facades\Facade;

class QueueQihu extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'queueqihu';
    }
}
