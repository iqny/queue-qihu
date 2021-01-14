<?php

namespace Qihu\Queue;

use Illuminate\Support\ServiceProvider;

class QueueQihuProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        // 发布配置文件
        $this->publishes([
            __DIR__ . '/config/queueqihu.php' => config_path('queueqihu.php'),
            __DIR__ . '/Commands/queueqihu.php' => app_path('Commands/queueqihu.php'),
            __DIR__ . '/Queue/TestQueue.php' => app_path('Queueqihu/TestQueue.php'),
            __DIR__ . '/Queue/OrderQueue.php' => app_path('Queueqihu/OrderQueue.php'),
        ]);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('queueqihu', function ($app) {
            return new QueueQihu($app['config']);
        });
    }
}
