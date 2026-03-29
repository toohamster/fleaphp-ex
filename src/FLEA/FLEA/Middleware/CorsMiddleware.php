<?php

namespace FLEA\Middleware;

/**
 * CORS 跨域资源共享中间件
 *
 * 处理跨域请求的中间件，自动添加 CORS 相关的 HTTP 响应头。
 * 支持预检请求（OPTIONS）自动响应。
 *
 * 配置项（通过 FLEA::setAppInf 设置）：
 * - corsAllowOrigin: 允许的源地址，默认 '*'
 * - corsAllowMethods: 允许的方法，默认 'GET,POST,PUT,PATCH,DELETE,OPTIONS'
 * - corsAllowHeaders: 允许的头部，默认 'Content-Type,Authorization,X-Requested-With'
 * - corsMaxAge: 预检请求缓存时间（秒），默认 86400
 *
 * 用法示例：
 * ```php
 * // 在配置文件中设置
 * return [
 *     'corsAllowOrigin' => 'https://example.com',
 *     'corsAllowMethods' => 'GET,POST,OPTIONS',
 * ];
 *
 * // 或在代码中动态设置
 * FLEA::setAppInf('corsAllowOrigin', 'https://example.com');
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class CorsMiddleware implements MiddlewareInterface
{
    /**
     * 处理 CORS 请求
     *
     * 添加 CORS 响应头并处理预检请求。
     *
     * @param callable $next 下一个中间件或请求处理器
     *
     * @return void
     */
    public function handle(callable $next): void
    {
        if (headers_sent()) {
            $next();
            return;
        }

        $origin  = \FLEA::getAppInf('corsAllowOrigin')  ?? '*';
        $methods = \FLEA::getAppInf('corsAllowMethods') ?? 'GET,POST,PUT,PATCH,DELETE,OPTIONS';
        $headers = \FLEA::getAppInf('corsAllowHeaders') ?? 'Content-Type,Authorization,X-Requested-With';
        $maxAge  = \FLEA::getAppInf('corsMaxAge')       ?? 86400;

        header("Access-Control-Allow-Origin: {$origin}");
        header("Access-Control-Allow-Methods: {$methods}");
        header("Access-Control-Allow-Headers: {$headers}");
        header("Access-Control-Max-Age: {$maxAge}");

        // OPTIONS 预检请求直接返回
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            http_response_code(204);
            exit;
        }

        $next();
    }
}
