<?php

namespace FLEA\Middleware;

/**
 * 中间件管道（洋葱模型实现）
 *
 * 负责按顺序执行中间件链，每个中间件可以：
 * - 在请求处理前执行逻辑
 * - 调用下一个中间件（通过 $next 回调）
 * - 在请求处理后执行逻辑
 *
 * 洋葱模型特点：
 * 中间件按顺序执行，形成嵌套结构，类似洋葱：
 * 外层中间件 → 内层中间件 → 核心应用 → 内层返回 → 外层返回
 *
 * 用法示例：
 * ```php
 * // 创建管道
 * $pipeline = \FLEA\Middleware\Pipeline::create();
 *
 * // 添加中间件（按添加顺序执行）
 * $pipeline->pipe(new CorsMiddleware())
 *          ->pipe(new AuthMiddleware())
 *          ->pipe(new RateLimitMiddleware());
 *
 * // 执行管道
 * $pipeline->run(function() {
 *     // 核心应用逻辑
 *     echo "Hello World";
 * });
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 * @see     \FLEA\Middleware\MiddlewareInterface
 */
class Pipeline
{
    /**
     * @var MiddlewareInterface[] 中间件队列
     */
    private array $middlewares = [];

    /**
     * 创建 Pipeline 实例
     *
     * @return self Pipeline 实例
     */
    public static function create(): self
    {
        return new self();
    }

    /**
     * 添加中间件到管道
     *
     * 中间件按添加顺序依次执行。
     *
     * 用法示例：
     * ```php
     * $pipeline->pipe(new CorsMiddleware())
     *          ->pipe(new AuthMiddleware());
     * ```
     *
     * @param MiddlewareInterface $middleware 中间件实例
     *
     * @return self 返回自身实例（支持链式调用）
     */
    public function pipe(MiddlewareInterface $middleware): self
    {
        $this->middlewares[] = $middleware;
        return $this;
    }

    /**
     * 执行中间件管道
     *
     * 使用 array_reduce 从后向前构建中间件调用链，
     * 形成洋葱模型嵌套结构。
     *
     * 用法示例：
     * ```php
     * $pipeline->run(function() {
     *     // 核心应用逻辑
     *     return Response::send(['status' => 'ok']);
     * });
     * ```
     *
     * @param callable $destination 最终要执行的目标（控制器/闭包）
     *
     * @return void
     */
    public function run(callable $destination): void
    {
        $pipeline = array_reduce(
            array_reverse($this->middlewares),
            fn(callable $carry, MiddlewareInterface $mw) => fn() => $mw->handle($carry),
            $destination
        );

        $pipeline();
    }
}
