<?php

namespace Qihu\Queue;

use Qihu\Queue\Drive\RedisFactory;
use Qihu\Queue\Signal\Signal;
use Illuminate\Config\Repository;

class QueueQihu
{
    protected $cfg;

    /**
     * 构造方法
     */
    public function __construct(Repository $config)
    {
        $this->cfg = $config->get('queueqihu');
    }

    public $running = true;
    public $pids = [];
    public $runner = [];
    public $redis = null;

    public function append($pid)
    {
        array_push($this->pids, $pid);
    }

    public function restart()
    {
        $pid = RedisFactory::createClient($this->cfg['redis'])->hGet("qihu:queue", 'monitor');
        return posix_kill($pid, SIGHUP);
    }

    public function kill()
    {
        $pid = RedisFactory::createClient($this->cfg['redis'])->hGet("qihu:queue", 'monitor');
        return posix_kill($pid, SIGTERM);
    }

    public function run($daemon)
    {
        if ($daemon) {
            $this->daemon();
        }
        if (PHP_OS == 'Linux') {
            cli_set_process_title("php:qihu monitor master");
        }
        $this->redis = RedisFactory::createClient($this->cfg['redis']);
        //var_dump($this->cfg['queue']);
        $this->redis->del("qihu:queue");
        $this->redis->hSet("qihu:queue", 'monitor', posix_getpid());
        //$ret = $redis->append('test',3);
        //$ret = $redis->get('test');
        //$this->redis->hSet("qihu:queue",'test',1);
        //$ret = $this->redis->hGet('qihu:queue','test');
        //var_dump($ret);
        //Signal::SetSigHandler([&$this, 'sigHandler']);
        $sleepTime = 5;
        while ($this->running) {
            foreach ($this->cfg['queue'] as $queueName => $cfg) {
                if (isset($cfg['run']) && $cfg['run']) {
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

    public function check($queueName, $cfg)
    {
        //var_dump($this->redis);
        $pid = $this->redis->hGet("qihu:queue", $queueName);
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
                cli_set_process_title("php:qihu {$queueName} slave");
            }
            $redisClient = RedisFactory::createClient($this->cfg['redis']);
            $redisClient->hSet("qihu:queue", $queueName, posix_getpid());
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
                exit(0);
            case SIGHUP:
                $this->stop();
                $this->cfg = require config_path('queueqihu.php');//重启重新读取配置
            default:
                break;
        }
    }

    private function stop()
    {
        foreach ($this->cfg['queue'] as $queueName => $cfg) {
            $pid = $this->redis->hGet("qihu:queue", $queueName);
            if ($pid && posix_kill($pid, 0)) {
                $ret = posix_kill($pid, SIGTERM);
                if ($ret) {
                    Logger::warning('monitor', "send stop $pid");
                    pcntl_waitpid($pid, $status);
                    //删除redis记录
                    $this->redis->hDel("qihu:queue", $queueName);
                }
            }
        }
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
