<?php

namespace Qihu\Queue;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;

class Logger
{

    public static function info($name, $msg)
    {
        $log = new \Monolog\Logger('local');
        $handle = new StreamHandler(storage_path("logs/queue/info/{$name}-" . date('Y-m-d') . '.log'), \Monolog\Logger::DEBUG);
        $handle->setFormatter(new LineFormatter(null, null, true, true));
        $log->pushHandler($handle);
        $log->info($msg);
    }

    public static function alert($name, $msg)
    {
        $log = new \Monolog\Logger('local');
        $handle = new StreamHandler(storage_path("logs/queue/alert/{$name}-" . date('Y-m-d') . '.log'), \Monolog\Logger::DEBUG);
        $handle->setFormatter(new LineFormatter(null, null, true, true));
        $log->pushHandler($handle);
        $log->alert($name, $msg);
    }

    public static function notice($name, $msg)
    {
        $log = new \Monolog\Logger('local');
        $handle = new StreamHandler(storage_path("logs/queue/notice/{$name}-" . date('Y-m-d') . '.log'), \Monolog\Logger::DEBUG);
        $handle->setFormatter(new LineFormatter(null, null, true, true));
        $log->pushHandler($handle);
        $log->notice($msg);
    }

    public static function debug($name, $msg)
    {
        $log = new \Monolog\Logger('local');
        $handle = new StreamHandler(storage_path("logs/queue/debug/{$name}-" . date('Y-m-d') . '.log'), \Monolog\Logger::DEBUG);
        $handle->setFormatter(new LineFormatter(null, null, true, true));
        $log->pushHandler($handle);
        $log->debug($msg);
    }

    public static function warning($name, $msg)
    {
        $log = new \Monolog\Logger('local');
        $handle = new StreamHandler(storage_path("logs/queue/warning/{$name}-" . date('Y-m-d') . '.log'), \Monolog\Logger::DEBUG);
        $handle->setFormatter(new LineFormatter(null, null, true, true));
        $log->pushHandler($handle);
        $log->warning($msg);
    }

    public static function critical($name, $msg)
    {
        $log = new \Monolog\Logger('local');
        $handle = new StreamHandler(storage_path("logs/queue/critical/{$name}-" . date('Y-m-d') . '.log'), \Monolog\Logger::DEBUG);
        $handle->setFormatter(new LineFormatter(null, null, true, true));
        $log->pushHandler($handle);
        $log->critical($msg);
    }

    public static function emergency($name, $msg)
    {
        $log = new \Monolog\Logger('local');
        $handle = new StreamHandler(storage_path("logs/queue/emergency/{$name}-" . date('Y-m-d') . '.log'), \Monolog\Logger::DEBUG);
        $handle->setFormatter(new LineFormatter(null, null, true, true));
        $log->pushHandler($handle);
        $log->emergency($msg);
    }

    public static function error($name, $msg)
    {
        $log = new \Monolog\Logger('local');
        $handle = new StreamHandler(storage_path("logs/queue/debug/{$name}-" . date('Y-m-d') . '.log'), \Monolog\Logger::DEBUG);
        $handle->setFormatter(new LineFormatter(null, null, true, true));
        $log->pushHandler($handle);
        $log->error($msg);
    }
}
