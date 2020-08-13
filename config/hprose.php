<?php

return [
    /*
     * 对外提供的服务名称
     */
    'service' => env('HPROSE_SERVICE'),

    /*
     * Debug 模式
     */
    'debug' => env('HPROSE_DEBUG', false),

    /*
     * 启动的服务类型，可选 socket / swoole
     * 选为 swoole 但未安装 swoole 扩展时，会使用 socket 方式
     */
    'server' => env('HPROSE_SERVER', 'socket'),

    /*
     * 监听地址端口
     */
    'uri' => env('HPROSE_URI', 'tcp://0.0.0.0:8888'),

    /*
     * 中间件
     */
    'middleware' => [
        \Poplary\LumenHprose\Middleware\ServerLoggerMiddleware::class,
    ],
];
