<?php

namespace FLEA\Middleware;

/**
 * 链路追踪 ID 中间件
 *
 * 框架内置，自动作为 Pipeline 最外层中间件。
 * 在每个请求中输出 X-Trace-Id 响应头。
 *
 * @package FLEA
 * @author toohamster
 * @version 2.3.0
 */
class TraceIdMiddleware implements MiddlewareInterface
{
    /**
     * 处理请求
     *
     * @param callable $next 下一个中间件或请求处理器
     *
     * @return void
     */
    public function handle(callable $next): void
    {
        // 前置设置 TraceID header，确保任何中间件短路都不会丢失
        \FLEA\Response::current()->withHeader(
            'X-Trace-Id',
            \FLEA\Context\TraceContext::getFullTraceId()
        );

        $next();
    }
}
