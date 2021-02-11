<?php

namespace Qihu\Queue;

use Qihu\Queue\Drive\RedisFactory;
use Qihu\Queue\Signal\Signal;
use Illuminate\Config\Repository;

class QueueQihu
{
    protected $cfg;
    const QUEUE_MONITOR_NAME = 'qihu:queue';
    public $running = true;
    public $pids = [];
    public $redis = null;

    /**
     * 构造方法
     */
    public function __construct(Repository $config)
    {
        $this->cfg = $config->get('queueqihu');
        $this->redis = RedisFactory::createClient($this->cfg['redis']);
    }

    public function append($pid)
    {
        array_push($this->pids, $pid);
    }

    public function restart(): bool
    {
        return posix_kill($this->getMonitorPid(), SIGHUP);
    }

    public function kill()
    {
        if (!posix_kill($this->getMonitorPid(), SIGTERM)) {
            $this->stop();
            exit(0);
        }
    }

    public function run($daemon)
    {
        if ($daemon) {
            $this->daemon();
        }
        if (PHP_OS == 'Linux') {
            cli_set_process_title(sprintf("%s monitor master", self::QUEUE_MONITOR_NAME));
        }
        //为了防止多次启动
        if ($this->checkMonitor()) {
            die();
        }
        $this->redis->del(self::QUEUE_MONITOR_NAME);
        $this->redis->hSet(self::QUEUE_MONITOR_NAME, 'monitor', posix_getpid());
        $sleepTime = 5;
        while ($this->running) {
            $this->setMonitor();
            foreach ($this->cfg['queue'] as $queueName => $cfg) {
                if (isset($cfg['run']) && $cfg['run']) {
                    $this->completingCfg($cfg);
                    $this->check($queueName, $cfg);
                }
            }
            Signal::SetSigHandler([&$this, 'sigHandler']);
            $pid = pcntl_waitpid(-1, $status, WNOHANG);
            if ($pid <= 0) {
                sleep($sleepTime);
                $sleepTime = 1;
            } else {
                Logger::warning('monitor', "process $pid exit");
            }
        }
    }

    private function completingCfg(&$cfg)
    {
        if ((isset($cfg['drive']) && empty($cfg['drive'])) || !isset($cfg['drive'])) {
            $cfg['drive'] = $this->cfg['connect']['drive'];//默认驱动;
        }
        $cfg['retry_time'] = isset($this->cfg['connect']['retry_time']) && !empty($this->cfg['connect']['retry_time']) ? $this->cfg['connect']['retry_time'] : 60;
        $cfg['process_name'] = self::QUEUE_MONITOR_NAME;
    }

    private function getMonitorPid()
    {
        return $this->redis->hGet(self::QUEUE_MONITOR_NAME, 'monitor');
    }

    public function check($queueName, $cfg)
    {
        //var_dump($this->redis);
        $pid = $this->redis->hGet(self::QUEUE_MONITOR_NAME, $queueName);
        if ($pid) {
            if (!intval($pid) || !posix_kill($pid, 0)) {
                $this->start($queueName, $cfg);
            }
        } else {
            $this->start($queueName, $cfg);

        }
        $this->redis->close();
    }

    private function start($queueName, $cfg)
    {
        $pid = pcntl_fork();
        if ($pid == -1) {
            Logger::error('monitor', "fork failed monitor");
            die('ERROR:fork failed monitor');
        } elseif ($pid) {
            return;
        } else {
            Logger::info('monitor', "start $queueName");
            if (PHP_OS == 'Linux') {
                cli_set_process_title(sprintf("%s %s slave",self::QUEUE_MONITOR_NAME,$queueName));
            }
            $redisClient = RedisFactory::createClient($this->cfg['redis']);
            $redisClient->hSet(self::QUEUE_MONITOR_NAME, $queueName, posix_getpid());
            $redisClient->close();
            Worker::start($queueName, $cfg);
        }

    }

    //信号事件回调
    public function sigHandler($signo)
    {
        //stop
        switch ($signo) {
            case SIGTERM:
                $this->stop();
                $this->exitClear();
                exit(0);
            case SIGHUP:
                $this->stop();
                $this->cfg = require config_path('queueqihu.php');//重启重新读取配置
                break;
            default:
                $this->exitClear();
                break;
        }
    }

    private function exitClear()
    {
        $this->redis->del('monitor');
    }

    private function stop()
    {
        foreach ($this->cfg['queue'] as $queueName => $cfg) {
            $pid = $this->redis->hGet(self::QUEUE_MONITOR_NAME, $queueName);
            if ($pid && posix_kill($pid, 0)) {
                $ret = posix_kill($pid, SIGTERM);
                if ($ret) {
                    Logger::warning('monitor', "send stop $pid");
                    pcntl_waitpid($pid, $status);
                    //删除redis记录
                    $this->redis->hDel(self::QUEUE_MONITOR_NAME, $queueName);
                }
            }
        }
    }

    private function checkMonitor()
    {
        return $this->redis->exists('monitor');
    }

    private function setMonitor()
    {
        $key = 'monitor';
        $this->redis->set($key, 1);
        $this->redis->expire($key, 10);
    }

    private function daemon()
    {
        $pid = pcntl_fork();
        if ($pid == -1) {
            die ("fork failed for daemon");
        } else if ($pid) {
            exit (0);
        } else {
            if (posix_setsid() == -1) {
                die ("could not detach from terminal");
            }
        }
    }
}
