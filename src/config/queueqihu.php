<?php
return [
    'connect' => [
        'drive' => env('QIHU_DRIVE', 'rabbitmq'),
        'retry_time'=> env('QIHU_RETRY_TIME',60) //异常断开，等待指定重试拉起
    ],
    'redis' => [
        'drive' => env('QIHU_REDIS_DRIVE', 'redis'),//predis
        'host' => env('QIHU_REDIS_HOST', '127.0.0.1'),//127.0.0.1
        'port' => env('QIHU_REDIS_PORT', '6379'),
        'password' => env('QIHU_REDIS_PASSWORD', ''),
    ],
    'rabbitmq' => [
        'host' => env('QIHU_RABBITMQ_HOST', '127.0.0.1'),
        'port' => env('QIHU_RABBITMQ_PORT', '5672'),
        'login' => env('QIHU_RABBITMQ_LOGIN', ''),
        'password' => env('QIHU_RABBITMQ_PASSWORD', ''),
        'exchange' => env('QIHU_RABBITMQ_EXCHANGE', 'my_exchange'),//交换机名
        'vhost' => env('QIHU_RABBITMQ_VHOST', '/'),//虚拟路径
        'routing'=>'route',
    ],
    'rocketmq' => [
        'host' => env('QIHU_ROCKETMQ_HOST', '127.0.0.1'),
        'access_key'=>env('QIHU_ROCKETMQ_ACCESS_KEY',''),
        'secret_key'=>env('QIHU_ROCKETMQ_SECRET_KEY',''),
        'num_of_messages'=>env('QIHU_ROCKETMQ_NUM_OF_MESSAGES',1),//一次最多消费5条(最多可设置为16条)
        'wait_seconds'=>env('QIHU_ROCKETMQ_WAIT_SECONDS',1)//长轮询时间1秒（最多可设置为30秒）
    ],
    'queue' => [
        'test' => [
            'class' => 'App\Queueqihu\TestQueue',
            'run' => true,
            'drive' => '',
            //如果驱动是rocketmq，以下需要配置
            'rocketmq'=>[
                'instance_id'=>'',
                'topic'=>'',
                'group_id'=>'',
            ],
            'worker_count' => 2,
            'max_exe_count' => 10000,
        ],
        'order' => [
            'class' => 'App\Queueqihu\OrderQueue',
            'run' => true,
            'drive' => '',
            //如果驱动是rocketmq，以下需要配置
            'rocketmq'=>[
                'instance_id'=>'',
                'topic'=>'',
                'group_id'=>'',
            ],
            'worker_count' => 2,
            'max_exe_count' => 10000,
        ],
        'log' => [
            'class' => 'App\Queueqihu\LogQueue',
            'run' => true,
            'drive' => 'redis',
            //如果驱动是rocketmq，以下需要配置
            'rocketmq'=>[
                'instance_id'=>'',
                'topic'=>'',
                'group_id'=>'',
            ],
            'worker_count' => 1,
            'max_exe_count' => 10000,
        ]
    ]
];
