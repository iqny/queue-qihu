<?php

namespace App\Console\Commands;

use Illuminate\Config\Repository;
use Illuminate\Console\Command;
use Qihu\Queue\QueueQihu as queue;

class queueqihu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:qihu {commend} {--daemon=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public $config;
    private $cli = null;

    /**
     * Create a new command instance.
     *
     * @param Repository $config
     */
    public function __construct(Repository $config)
    {
        $this->config = $config;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $commend = $this->argument('commend');
        $daemon = $this->option('daemon');
        $this->cli = new queue($this->config);
        switch ($commend) {
            case 'start':
                echo 'start...' . PHP_EOL;
                echo 'start success' . PHP_EOL;
                $this->start($daemon);
                break;
            case 'stop':
                echo 'stop...' . PHP_EOL;
                echo 'stop success' . PHP_EOL;
                $this->stop();
                break;
            case 'restart':
                echo 'restart...' . PHP_EOL;
                echo 'restart success' . PHP_EOL;
                $this->restart();
                break;
            default:
                break;
        }
        //$this->cli = new QueueQihu($this->config);
    }

    private function start($daemon)
    {
        $this->cli->run($daemon);
    }
    private function restart()
    {
        $this->cli->restart();
    }

    private function stop()
    {
        $this->cli->kill();
    }
}
