<?php

namespace Qihu\Queue\Queue;

use Qihu\Queue\Lock;
use Qihu\Queue\Logger;
use Qihu\Queue\ConnPool;
use Qihu\Queue\Signal\Signal;

abstract class BaseQueue
{
    private static $running = true;
    protected $data = [];
    protected $queueName = '';
    private $pStartTime;
    private $index = '';
    private $count = 0;
    private static $rabbitmqExit = true;

    public function __construct($queueName)
    {
        $this->queueName = $queueName;
        $this->pStartTime = time();

    }

    abstract function parse();

    public function run($count = 0)
    {
        $this->count = $count;
        while (self::$running) {
            Signal::SetSigHandler([self::class, 'sigHandler']);
            ConnPool::getQueueClient($this->queueName)->get($this->queueName, function ($envelope, $queue) {
                Signal::SetSigHandler([self::class, 'sigHandler']);
                $data = $envelope->getBody();

                if (empty($data)) {
                    sleep(1);
                    return;
                }
                $this->index = md5($data . microtime(true));
                //echo 'md5*' . $this->index . PHP_EOL;
                //进程锁，同一数据同时只能在一个task执行
                if (!Lock::acquire($this->index, 60, 1)) {
                    //获取锁失败
                    return;
                }
                $this->setData($data);
                $ret = $this->parse();
                Lock::release($this->index);//释放锁
                if ($ret) {
                    if ($queue->ack($envelope->getDeliveryTag())) {
                        $this->info("ack ok");
                    } else {
                        $this->info("ack fail");
                    }
                }
                self::$rabbitmqExit = false; //用于信号退出进程
                $this->count--;
                if ($this->count <= 0) {
                    self::$running = false;
                    exit(0);
                }
                $newTime = date('H', time());
                $startTime = date('H', $this->pStartTime);
                if (!self::$running || $newTime != $startTime) {
                    self::$running = false;
                    exit(0);
                }
                self::$rabbitmqExit = true;//step 3 用于rabbitmq队列信号退出
            });
        }
    }

    protected function setData($data)
    {
        $this->data = json_decode($data, true, JSON_UNESCAPED_UNICODE);
    }

    public static function sigHandler($signo)
    {
        if (self::$rabbitmqExit) {
            self::$running = false;
            exit(0);
        }
        self::$running = false;
    }

    private function setLogMsg($msg): string
    {
        if (!is_string($msg)) {
            $msg = var_export($msg, true);
        }
        return sprintf("%s %s", $this->index, $msg);
    }

    public function info($msg)
    {
        $msg = $this->setLogMsg($msg);
        Logger::info($this->queueName, $msg);
    }

    public function alert($msg)
    {
        $msg = $this->setLogMsg($msg);
        Logger::alert($this->queueName, $msg);
    }

    public function notice($msg)
    {
        $msg = $this->setLogMsg($msg);
        Logger::notice($this->queueName, $msg);
    }

    public function debug($msg)
    {
        $msg = $this->setLogMsg($msg);
        Logger::debug($this->queueName, $msg);
    }

    public function warning($msg)
    {
        $msg = $this->setLogMsg($msg);
        Logger::warning($this->queueName, $msg);
    }

    public function critical($msg)
    {
        $msg = $this->setLogMsg($msg);
        Logger::critical($this->queueName, $msg);
    }

    public function emergency($msg)
    {
        $msg = $this->setLogMsg($msg);
        Logger::emergency($this->queueName, $msg);
    }

    public function error($msg)
    {
        $msg = $this->setLogMsg($msg);
        Logger::info($this->queueName, $msg);
    }
}
