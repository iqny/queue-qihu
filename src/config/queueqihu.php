<?php
return [
    'connect' => [
        'drive' => env('QIHU_DRIVE', 'rabbitmq'),
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
    ],
    'queue' => [
        'test' => [
            'class' => 'App\Queueqihu\TestQueue',
            'run' => true,
            'drive' => '',
            'mod' => 'fork',
            'worker_count' => 2,
            'max_exe_count' => 10000,
        ],
        'order' => [
            'class' => 'App\Queueqihu\OrderQueue',
            'run' => true,
            'drive' => '',
            'mod' => 'fork',
            'worker_count' => 2,
            'max_exe_count' => 10000,
        ],
        'log' => [
            'class' => 'App\Queueqihu\LogQueue',
            'run' => true,
            'drive' => 'redis',
            'mod' => 'fork',
            'worker_count' => 1,
            'max_exe_count' => 10000,
        ]
    ]
];
