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
 * - 短路请求（返回 Response 对象，不再调用 $next）
 *
 * 用法示例：
 * ```php
 * class AuthMiddleware implements \FLEA\Middleware\MiddlewareInterface
 * {
 *     public function handle(callable $next)
 *     {
 *         // 请求处理前：验证用户身份
 *         if (!isset($_SESSION['user_id'])) {
 *             return \FLEA\Response::error('未授权', 401); // 短路
 *         }
 *
 *         // 继续执行下一个中间件或控制器
 *         return $next();
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
     * @return mixed 返回 Response 表示短路，返回 $next() 的结果表示继续
     */
    public function handle(callable $next);
}
