<?php
/**
 * FleaPHP 内置服务器路由脚本
 *
 * 用法：
 *   php -S 127.0.0.1:8081 -t {projectDir}/public bin/router.php
 */

// 从环境变量获取项目根目录，默认当前目录
$projectDir = getenv('FLEA_PROJECT_DIR') ?: getcwd();

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 静态资源直接返回（相对于文档根目录 {projectDir}/public）
if (is_file($_SERVER['DOCUMENT_ROOT'] . $uri) && preg_match('/\.(css|js|png|jpg|gif|ico|svg|pdf|zip)$/i', $uri)) {
    return false;
}

// 所有其他请求转发到 {projectDir}/public/index.php
$_SERVER['PATH_INFO'] = $uri;
require $projectDir . '/public/index.php';
