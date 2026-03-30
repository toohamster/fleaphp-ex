<?php

namespace FLEA\Middleware;

/**
 * 中间件接口
 *
 * 所有中间件必须实现此接口，定义统一的 handle() 方法签名。
 *
 * 中间件是洋葱模型的核心，每个中间件可以：
 * - 在调用 $next 前执行逻辑（请求处理前）
 * - 调用 $next() 将请求传递给下一个中间件
 * - 在调用 $next 后执行逻辑（请求处理后）
 * - 短路请求（不调用 $next，直接返回响应）
 *
 * 用法示例：
 * ```php
 * class AuthMiddleware implements \FLEA\Middleware\MiddlewareInterface
 * {
 *     public function handle(callable $next): void
 *     {
 *         // 请求处理前：验证用户身份
 *         if (!isset($_SESSION['user_id'])) {
 *             \FLEA\Response::error('未授权', 401);
 *             return; // 短路，不再继续
 *         }
 *
 *         // 继续执行下一个中间件或控制器
 *         $next();
 *
 *         // 请求处理后：记录访问日志
 *         log_message('用户访问', 'info');
 *     }
 * }
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 * @see     \FLEA\Middleware\Pipeline
 */
interface MiddlewareInterface
{
    /**
     * 处理请求
     *
     * 中间件的核心逻辑在此方法中实现。
     *
     * @param callable $next 调用下一个中间件或最终处理器
     *                       调用 $next() 将请求传递给下一个中间件，
     *                       返回后表示下一个中间件/控制器已执行完毕
     *
     * @return void
     */
    public function handle(callable $next): void;
}
