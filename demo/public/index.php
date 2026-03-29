<?php

require_once __DIR__ . '/../vendor/autoload.php';

// 加载 .env 环境变量（必须指定完整路径）
\FLEA::loadEnv(__DIR__ . '/../.env');

// 自动注册 App 命名空间
class_loader()->addPsr4('FleaPhpDemo\\', __DIR__ . '/../App/');

// 加载配置
\FLEA::loadAppInf(__DIR__ . '/../App/Config.php');

// 加载路由配置（在 runMVC 之前注册路由）
if (file_exists(__DIR__ . '/../App/routes.php')) {
    require_once __DIR__ . '/../App/routes.php';
}

// 运行 MVC 应用
\FLEA::runMVC();
