<?php

namespace Qihu\Queue;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use phpDocumentor\Reflection\Types\Static_;

/**
 * @method static void info($name, $msg,$context=[])
 * @method static void alert($name, $msg,$context=[])
 * @method static void notice($name, $msg,$context=[])
 * @method static void debug($name, $msg,$context=[])
 * @method static void warning($name, $msg,$context=[])
 * @method static void critical($name, $msg,$context=[])
 * @method static void emergency($name, $msg,$context=[])
 * @method static void error($name, $msg,$context=[])
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
                if(isset(self::$pool[$type][$queue])){
                    return self::$pool[$type][$queue];
                }
                if (!is_null(self::$callHandel)){
                    self::$pool[$type][$queue] = call_user_func(self::$callHandel,$queue);
                }
                return self::$pool[$type][$queue];
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
        $context = $arguments[2]??[];
        if(!is_array($context)){
            $context = [$context];
        }
        $log->$name($arguments[1],$context);
    }
}
