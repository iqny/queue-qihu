<?php

namespace Qihu\Queue\Queue;

use Qihu\Queue\Lock;
use Qihu\Queue\Logger;
use Qihu\Queue\ConnPool;
use Qihu\Queue\Signal\Signal;

abstract class BaseQueue
{
    public static $running = true;
    private $data = [];
    protected $queueName = '';
    private $pStartTime;
    private $index = '';
    private $count = 0;
    public static $rabbitmqExit = true;
    protected $failRetry = false; //是否失败重试
    protected $failRetrySleepTime = 3; //失败后sleep多少s进入队列
    protected $retryMaxNum = 3;//重试次数
    protected $retryMaxNumError = 10;//进入错误队列最多重试的次数
    private $defaultDrive;//队列启动的驱动[redis|rabbitmq]
    private $conn;
    private $queueError;//错误队列名

    public function __construct($queueName, $drive)
    {
        $this->queueName = $queueName;
        $this->queueError = $queueName . '_error';
        $this->pStartTime = time();
        $this->setDefaultDrive($drive);
        $this->conn = ConnPool::getQueueClient($this->queueName);
    }

    abstract function parse():bool;

    public function run($count = 0)
    {
        $this->count = $count;
        while (self::$running) {
            Signal::SetSigHandler([self::class, 'sigHandler']);
            $this->conn->get($this->queueName, function ($envelope, $queue) {
                $newTime = date('H', time());
                $startTime = date('H', $this->pStartTime);
                if (!self::$running || $newTime != $startTime) {
                    self::$running = false;
                    exit(0);
                }
                Signal::SetSigHandler([self::class, 'sigHandler']);
                $data = $envelope->getBody();

                if (empty($data)) {
                    sleep(1);
                    return;
                }
                $this->index = md5($data);
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
                } else {
                    //处理失败 redis驱动才进行重试
                    if ($this->getDefaultDrive() == 'redis') {
                        $this->retryFail();
                    }
                }
                self::$rabbitmqExit = false; //用于信号退出进程
                $this->count--;
                if ($this->count <= 0) {
                    self::$running = false;
                    exit(0);
                }
                self::$rabbitmqExit = true;//step 3 用于rabbitmq队列信号退出
            });
        }
    }

    protected function getData(): array
    {
        return $this->data;
    }

    private function setData($data)
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

    private function retryFail()
    {
        if ($this->failRetry) {
            if (!isset($this->data['retry_num'])) {
                $this->data['retry_num'] = 0;
            }
            if ($this->data['retry_num'] <= $this->retryMaxNum) {
                if (!empty($this->failRetrySleepTime)) {
                    sleep($this->failRetrySleepTime);
                }
                //进去队列
                ++$this->data['retry_num'];
                $this->debug("重新加入队列: {$this->retryMaxNum}");
                $this->conn->put($this->queueName, json_encode($this->data));
            } else {
                $this->debug("重试达到指定次数: {$this->data['retry_num']}");
                if (!isset($this->data['retry_error_num'])) {
                    $this->data['retry_error_num'] = 0;
                }
                if ($this->data['retry_error_num'] <= $this->retryMaxNumError) {
                    sleep(1);
                    //进行错误队列
                    ++$this->data['retry_error_num'];
                    $this->debug("加入错误队列: {$this->data['retry_error_num']}");
                    $this->conn->put($this->queueError, json_encode($this->data));
                } else {
                    $this->debug("错误队列重试达到指定次数: {$this->retryMaxNumError}");
                }
            }
        }
    }

    private function setDefaultDrive($drive)
    {
        $this->defaultDrive = $drive;
    }

    private function getDefaultDrive()
    {
        return $this->defaultDrive;
    }

    private function setLogMsg($msg): string
    {
        if (!is_string($msg)) {
            $msg = var_export($msg, true);
        }
        return sprintf("%s %s", $this->index, $msg);
    }

    public function info($msg,$context=[])
    {
        $msg = $this->setLogMsg($msg);
        Logger::info($this->queueName, $msg,$context);
    }

    public function alert($msg,$context=[])
    {
        $msg = $this->setLogMsg($msg);
        Logger::alert($this->queueName, $msg,$context);
    }

    public function notice($msg,$context=[])
    {
        $msg = $this->setLogMsg($msg);
        Logger::notice($this->queueName, $msg);
    }

    public function debug($msg,$context=[])
    {
        $msg = $this->setLogMsg($msg);
        Logger::debug($this->queueName, $msg,$context);
    }

    public function warning($msg,$context=[])
    {
        $msg = $this->setLogMsg($msg);
        Logger::warning($this->queueName, $msg,$context);
    }

    public function critical($msg,$context=[])
    {
        $msg = $this->setLogMsg($msg);
        Logger::critical($this->queueName, $msg,$context);
    }

    public function emergency($msg,$context=[])
    {
        $msg = $this->setLogMsg($msg);
        Logger::emergency($this->queueName, $msg,$context);
    }

    public function error($msg,$context=[])
    {
        $msg = $this->setLogMsg($msg);
        Logger::info($this->queueName, $msg,$context);
    }
}
