<?php

/**
 * 演示应用配置
 *
 * 框架默认配置已在 FLEA\Config\Defaults 中定义
 * 此处仅覆盖 demo 应用特有的配置
 */

return [
    // 控制器配置
    'defaultController' => 'Post',
    'defaultAction' => 'index',
    'controllerClassPrefix' => 'FleaPhpDemo\\Controller\\',

    // URL 模式
    'urlScriptName' => '',
    'urlLowerChar' => false,

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
    'displayErrors' => env('APP_DEBUG', true),
    'friendlyErrorsMessage' => true,
    'displaySource' => true,

    // Session 配置
    'sessionProvider' => null,
    'autoSessionStart' => false,
];
