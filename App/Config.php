<?php

return [
    // 数据库配置
    'dbDSN' => [
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => '3306',
        'login' => 'root',
        'password' => '11111111',
        'database' => 'blog',
        'charset' => 'utf8mb4',
    ],

    // 控制器配置
    'controllerAccessor' => 'controller',
    'actionAccessor' => 'action',
    'defaultController' => 'Post',
    'defaultAction' => 'index',

    // URL 配置
    'urlMode' => URL_STANDARD,
    'urlBootstrap' => 'index.php',

    // 日志配置
    'logEnabled' => false,
    'logProvider' => null,

    // 调度器
    'dispatcher' => \FLEA\Dispatcher\Simple::class,

    // 模板引擎
    'view' => \FLEA\View\Simple::class,
    'viewConfig' => [
        'templateDir' => __DIR__ . '/View',
        'cacheDir' => __DIR__ . '/../cache',
        'cacheLifeTime' => 900,
        'enableCache' => false,
    ],

    // 错误显示（开发环境）
    'displayErrors' => true,
    'friendlyErrorsMessage' => true,
    'displaySource' => true,

    // 缓存目录
    'internalCacheDir' => __DIR__ . '/../cache',
];
