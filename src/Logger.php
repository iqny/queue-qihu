<?php

namespace Qihu\Queue;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use phpDocumentor\Reflection\Types\Static_;

/**
 * @method static void info($name, $msg)
 * @method static void alert($name, $msg)
 * @method static void notice($name, $msg)
 * @method static void debug($name, $msg)
 * @method static void warning($name, $msg)
 * @method static void critical($name, $msg)
 * @method static void emergency($name, $msg)
 * @method static void error($name, $msg)
 */
class Logger
{
    private static $pool = [];
    private static $log = null;
    private static $drive = 'default';
    private static $callHandel = null;
    /**
     * @param $type
     * @param $queue
     * @return \Monolog\Logger
     */
    private static function getInstance($type, $queue): \Monolog\Logger
    {
        switch (self::$drive) {
            case 'default':
                return self::defaultHandle($type,$queue);
            default:
                if(isset(self::$pool[$type])){
                    return self::$pool[$type];
                }
                if (!is_null(self::$callHandel)){
                    self::$pool[$type] = call_user_func(self::$callHandel);
                }
                return self::$pool[$type];
        }
    }
    private static function defaultHandle($type,$queue): \Monolog\Logger
    {
        $dataUnique = date('Y-m-d');
        $datePath = $dataUnique . "/{$type}/{$queue}";
        if (isset(self::$pool[$dataUnique][$queue][$type]) && self::$pool[$dataUnique][$queue][$type] != null) {
            return self::$pool[$dataUnique][$queue][$type];
        }
        $log = new \Monolog\Logger('qihu');
        $handle = new StreamHandler(storage_path('logs/queue/' . $datePath . '.log'), \Monolog\Logger::DEBUG);
        $handle->setFormatter(new LineFormatter(null, null, true, true));
        $log->pushHandler($handle);
        $prevDate = date('Y-m-d', strtotime('-1 day'));
        if (isset(self::$pool[$prevDate]) && !empty(self::$pool[$prevDate])) {
            self::$pool[$prevDate] = [];
        }
        self::$pool[$dataUnique][$queue][$type] = &$log;
        return $log;
    }
    public static function setLogger(callable $handle){
        self::$drive = '';
        self::$callHandel = $handle;
    }

    public static function __callStatic($name, $arguments)
    {
        $log = self::getInstance($name, $arguments[0]);
        $log->$name($arguments[1]);
    }
}
