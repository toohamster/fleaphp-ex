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
    'defaultController' => 'Post',
    'defaultAction' => 'index',
    'controllerClassPrefix' => 'FleaPhpDemo\\Controller\\',

    // URL 模式
    'urlMode' => URL_ROUTER,
    'urlScriptName' => '',
    'urlLowerChar' => false,

    // 日志配置
    'logEnabled' => true,
    'logProvider' => null,
    'logFilename' => 'app.log',

    // 调度器
    'dispatcher' => \FLEA\Dispatcher\Simple::class,

    // 模板引擎
    'view' => \FLEA\View\Simple::class,
    'viewConfig' => [
        'templateDir' => __DIR__ . '/View',
        'cacheDir' => __DIR__ . '/../../cache',
        'cacheLifeTime' => 900,
        'enableCache' => false,
    ],

    // 错误显示（开发环境）
    'displayErrors' => true,
    'friendlyErrorsMessage' => true,
    'displaySource' => true,

    // 缓存配置
    'cacheProvider' => \FLEA\Cache\FileCache::class,
    'internalCacheDir' => __DIR__ . '/../../cache',

    // Session 配置
    'sessionProvider' => null,
    'autoSessionStart' => false,

    // JWT 配置（如使用 API）
    'jwtSecret' => 'change-me-to-a-secure-secret',
    'jwtTtl' => 7200,
];
