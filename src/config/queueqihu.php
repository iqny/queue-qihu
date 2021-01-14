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
            'class' => 'Qihu\Queue\Queue\TestQueue',
            //'queue' => 'test',
            'run' => true,
            'mod' => 'fork',
            'worker_count' => 2,
            'max_exe_count' => 1000,
        ],
        'order' => [
            'class' => 'Qihu\Queue\Queue\OrderQueue',
            //'queue' => 'test',
            'run' => true,
            'mod' => 'fork',
            'worker_count' => 2,
            'max_exe_count' => 1000,
        ]
    ]
];
