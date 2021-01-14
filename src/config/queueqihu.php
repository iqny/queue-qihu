<?php
return [
    'connect' => [
        'drive' => 'rabbitmq'
    ],
    'redis' => [
        'drive' => 'redis',//predis
        'host' => '127.0.0.1',//127.0.0.1
        'port' => '6379',
        'password' => '',
    ],
    'rabbitmq' => [
        'host' => '127.0.0.1',
        'port' => '5672',
        'login' => '',
        'password' => '',
        'exchange' => 'my_exchange',//交换机名
        'vhost' => '/',//虚拟路径
    ],
    'queue' => [
        'test' => [
            'class' => 'App\Queueqihu\TestQueue',
            //'queue' => 'test',
            'run' => true,
            'mod' => 'fork',
            'worker_count' => 2,
            'max_exe_count' => 1000,
        ],
        'order' => [
            'class' => 'App\Queueqihu\OrderQueue',
            //'queue' => 'test',
            'run' => true,
            'mod' => 'fork',
            'worker_count' => 2,
            'max_exe_count' => 1000,
        ]
    ]
];
