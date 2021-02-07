# queue-qihu
<p>redis 支持php-redis扩展，predis。默认安装predis,如果选择redis扩展需要自行安装</p>
rabbitmq 支持php-amqp。如果选择rabbitmq需安装php-amqp扩展
<p>按需求安装对应版本：</p>
<p>redis扩展：http://pecl.php.net/package/redis</p>
<p>amqp扩展：http://pecl.php.net/package/amqp</p>

选择rabbitmq做队列，需要安装下php-signal-handler扩展，因为php-amqp在consume()阻塞情况下pcntl_signal失效
【建议安装】https://github.com/rstgroup/php-signal-handler
```
第一步：composer require iqny/queue
```
```
第二步：php artisan vendor:publish --provider="Qihu\Queue\QueueQihuProvider"
```
```
第三步：在.env文件添加如下配置
QIHU_DRIVE=rabbitmq
QIHU_REDIS_HOST=127.0.0.1
QIHU_REDIS_DRIVE=redis
QIHU_REDIS_PORT=6379
QIHU_REDIS_PASSWORD=null
QIHU_RABBITMQ_HOST=127.0.0.1
QIHU_RABBITMQ_PORT=5672
QIHU_RABBITMQ_LOGIN=
QIHU_RABBITMQ_PASSWORD=null
QIHU_RABBITMQ_EXCHANGE=my_exchange
QIHU_RABBITMQ_VHOST="/"
```
```
第四步：操作完以上步骤，在app/Queueqihu目录下编写任务
```
### 队列特点：
```
1、支持平滑的重启队列重新读取配置文件
2、在默认驱动情况下，可以配置某个队列启动指定驱动[redis|rabbitmq]
3、rabbitmq默认consume阻塞，是否要ack根据当前执行的任务返回是true|false
4、默认队列任务每1个小时自动退出1次，防止内存溢出。
5、可以配置队列在执行指定任务次数自动退出，防止内存溢出
6、日志目录在storage/logs/queue，按每天创建目录，
   支持多种日志类型记录：debug|info|alert|notice|warning|critical|emergency|error
7、使用redis驱动，可以开启失败重试。
8、rabbitmq、redis连接异常断开重启机制,可配置时间
```
命令：
```
php artisan queue:qihu start              启动
```
```
php artisan queue:qihu start --daemon=1   守护进程启动
```
```
php artisan queue:qihu stop               停止
```
```
php artisan queue:qihu restart            重启
```

### Example
公共函数：qihuPush($queueName,$msg);
```php
<?php
for ($i = 0; $i < 10000; $i++) {
    $msg = [
        'message_order' => $i
    ];
    qihuPush('order', $msg);
    $msg = [
        'message_test' => $i
    ];
    qihuPush('test', $msg);
    $msg = [
        'message_log' => $i
    ];
    qihuPush('log',$msg);
}
?>
