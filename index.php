<?php

require_once 'vendor/autoload.php';

// 自动注册 App 命名空间
class_loader()->addPsr4('App\\', __DIR__ . '/App/');

// 加载配置
\FLEA::loadAppInf('App/Config.php');

// 运行 MVC 应用
\FLEA::runMVC();
