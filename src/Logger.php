<?php

namespace Qihu\Queue;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

class Logger
{

    public static function info($name, $msg)
    {
        $log = new \Monolog\Logger('local');
        $handle = new StreamHandler(storage_path('logs/queue/'.date('Y-m-d')."/info/{$name}.log"), \Monolog\Logger::DEBUG);
        $handle->setFormatter(new LineFormatter(null, null, true, true));
        $log->pushHandler($handle);
        $log->info($msg);
    }

    public static function alert($name, $msg)
    {
        $log = new \Monolog\Logger('local');
        $handle = new StreamHandler(storage_path('logs/queue/'.date('Y-m-d')."/alert/{$name}.log"), \Monolog\Logger::DEBUG);
        $handle->setFormatter(new LineFormatter(null, null, true, true));
        $log->pushHandler($handle);
        $log->alert($name, $msg);
    }

    public static function notice($name, $msg)
    {
        $log = new \Monolog\Logger('local');
        $handle = new StreamHandler(storage_path('logs/queue/'.date('Y-m-d')."/notice/{$name}.log"), \Monolog\Logger::DEBUG);
        $handle->setFormatter(new LineFormatter(null, null, true, true));
        $log->pushHandler($handle);
        $log->notice($msg);
    }

    public static function debug($name, $msg)
    {
        $log = new \Monolog\Logger('local');
        $handle = new StreamHandler(storage_path('logs/queue/'.date('Y-m-d')."/debug/{$name}.log"), \Monolog\Logger::DEBUG);
        $handle->setFormatter(new LineFormatter(null, null, true, true));
        $log->pushHandler($handle);
        $log->debug($msg);
    }

    public static function warning($name, $msg)
    {
        $log = new \Monolog\Logger('local');
        $handle = new StreamHandler(storage_path('logs/queue/'.date('Y-m-d')."/warning/{$name}.log"), \Monolog\Logger::DEBUG);
        $handle->setFormatter(new LineFormatter(null, null, true, true));
        $log->pushHandler($handle);
        $log->warning($msg);
    }

    public static function critical($name, $msg)
    {
        $log = new \Monolog\Logger('local');
        $handle = new StreamHandler(storage_path('logs/queue/'.date('Y-m-d')."/critical/{$name}.log"), \Monolog\Logger::DEBUG);
        $handle->setFormatter(new LineFormatter(null, null, true, true));
        $log->pushHandler($handle);
        $log->critical($msg);
    }

    public static function emergency($name, $msg)
    {
        $log = new \Monolog\Logger('local');
        $handle = new StreamHandler(storage_path('logs/queue/'.date('Y-m-d')."/emergency/{$name}.log"), \Monolog\Logger::DEBUG);
        $handle->setFormatter(new LineFormatter(null, null, true, true));
        $log->pushHandler($handle);
        $log->emergency($msg);
    }

    public static function error($name, $msg)
    {
        $log = new \Monolog\Logger('local');
        $handle = new StreamHandler(storage_path('logs/queue/'.date('Y-m-d')."/error/{$name}.log"), \Monolog\Logger::DEBUG);
        $handle->setFormatter(new LineFormatter(null, null, true, true));
        $log->pushHandler($handle);
        $log->error($msg);
    }
}
