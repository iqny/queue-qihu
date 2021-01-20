<?php

namespace Qihu\Queue;

use Qihu\Queue\Queue\BaseQueue;
use Qihu\Queue\Signal\Signal;

class Worker
{
    private static $pids = [];
    private static $running = true;
    private static $cfg = [];
    private static $queues = [];

    private static function create($queueName,$drive)
    {
        $class = self::$cfg['class'];//'Qihu\Queue\Queue\\' . ucfirst($queueName) . 'Queue';
        if (!class_exists($class)) {
            self::$running = false;
            Logger::error("$queueName", "$class not found!");
            die("$class not found!");
        }
        $queue = new $class($queueName,$drive);
        if (!$queue instanceof BaseQueue) {
            self::$running = false;
            Logger::error("$queueName", "$class not implement Qihu\Queue\Queue\BaseQueue.");
            die("$class not implement Qihu\Queue\Queue\BaseQueue.");
        }
        //var_dump($queue);
        $queue->run(self::$cfg['max_exe_count']);
    }

    public static function start($queueName, $cfg)
    {
        self::$cfg = $cfg;
        while (self::$running) {
            for ($i = 0; $i < $cfg['worker_count']; $i++) {
                if (!empty(self::$pids[$i]) && posix_kill(self::$pids[$i], 0)) {
                    continue;
                } else {
                    $pid = pcntl_fork();
                    if ($pid == -1) {
                        Logger::error("$queueName", "fork failed monitor");
                        die('ERROR:fork failed monitor');
                    } elseif ($pid) {
                        self::$pids[$i] = $pid;
                        self::$queues[$i] = $queueName;
                        continue;
                    } else {
                        if (PHP_OS == 'Linux') {
                            cli_set_process_title("php:qihu {$queueName} worker[$i]");
                        }
                        //cli_set_process_title("superman php master process");
                        self::create($queueName,$cfg['drive']);
                        //echo '退出';
                        exit(0);
                        //$this->work($queueName);
                    }
                }
            }
            Signal::SetSigHandler([self::class, 'sigHandler']);
            $pid = pcntl_waitpid(-1, $status, WNOHANG);
            //var_dump("pid===$pid");
            if ($pid <= 0) {
                sleep(2);
            } else {
                Logger::warning("{$queueName}", "process $pid exit");
            }
        }
    }

    public static function sigHandler($signo)
    {
        foreach (self::$pids as $i => $pid) {
            $queueName = self::$queues[$i];
            Logger::warning("{$queueName}", "kill pid $pid;signo:$signo");
            if (!posix_kill($pid, 0)) {
                Logger::warning("{$queueName}", "process $pid has been exit(zombe);signo:$signo");
                continue;
            }
            $ret = posix_kill($pid, SIGINT);
            if ($ret) {
                $status = "";
                pcntl_waitpid($pid, $status);
                Logger::warning("{$queueName}", "process $pid is exit;signo:$signo");
            } else {
                Logger::warning("{$queueName}", "send signal to process $pid failed;signo:$signo");
            }
        }
        self::$running = false;
        exit ();
    }
}
