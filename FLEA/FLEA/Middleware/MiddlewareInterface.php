<?php

namespace FLEA\Middleware;

interface MiddlewareInterface
{
    /**
     * 处理请求
     * @param callable $next 调用下一个中间件或最终处理器
     */
    public function handle(callable $next): void;
}
