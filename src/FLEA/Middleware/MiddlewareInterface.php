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
 * - 短路请求（不调用 $next，通过 Response::current() 设置响应状态）
 *
 * 用法示例：
 * ```php
 * class AuthMiddleware implements \FLEA\Middleware\MiddlewareInterface
 * {
 *     public function handle(callable $next): void
 *     {
 *         // 请求处理前：验证用户身份
 *         if (!$this->validate()) {
 *             \FLEA\Response::current()->withStatus(401)
 *                 ->setView(\FLEA\View::json(['error' => 'Unauthorized']));
 *             return; // 短路
 *         }
 *
 *         // 继续执行下一个中间件或控制器
 *         $next();
 *     }
 * }
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.3.0
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
