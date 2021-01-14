<?php

namespace Qihu\Queue;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Qihu\Queue\Drive\RedisFactory;
use Qihu\Queue\Signal\Signal;
use Illuminate\Config\Repository;

class QueueQihu
{
    protected $cfg;
    private $logger;

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

    public function test()
    {
        /*$writer = app('log');
        $writer->alert("消息内容");*/
        /*$log = new \Monolog\Logger('queue');
        $handle = new StreamHandler(storage_path('logs/queue/p-' . date('Y-m-d') . '.log'), \Monolog\Logger::DEBUG);
        $handle->setFormatter(new LineFormatter(null, null, true, true));
        $log->pushHandler($handle);
        $log->info("info。。。。。。。。。。");*/
        while (true) {
            sleep(2);
            Logger::info("monitor", "check 123213");
        }
        //QueueHelper::getQueueClient()->put('test',"aldk");
        $ret = QueueHelper::getQueueClient()->get('test');
        var_dump($ret);
        return 1;//config('queueqihu');
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

    public function run()
    {
        $this->daemon();
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
        Logger::info('monitor', "check $queueName");
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
            if (PHP_OS == 'Linux') {
                cli_set_process_title("php:qihu {$queueName} slave");
            }
            $redisClient = RedisFactory::createClient($this->cfg['redis']);
            $redisClient->hSet("qihu:queue", $queueName, posix_getpid());
            $redisClient->close();
            Worker::start($queueName, $cfg, 1);
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
