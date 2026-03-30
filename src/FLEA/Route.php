<?php

namespace FLEA;

/**
 * 单条路由定义，支持链式命名
 *
 * 用于定义路由配置，支持通过 name() 方法为路由指定名称，
 * 用于反向生成 URL。
 *
 * 用法示例：
 * ```php
 * // 注册命名路由
 * Router::get('/users/{id}', 'UserController@show')->name('user.show');
 *
 * // 通过名称生成 URL
 * $url = Router::urlFor('user.show', ['id' => 1]);
 * // 返回：/users/1
 *
 * // 链式调用
 * Router::get('/api/users', 'ApiController@users')
 *     ->name('api.users')
 *     ->middleware([new AuthMiddleware()]);
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 * @see     \FLEA\Router
 */
class Route
{
    /**
     * @var array 路由配置数组（引用）
     */
    private array $route;

    /**
     * @var array 命名路由索引（引用）
     */
    private array $namedRoutes;

    /**
     * 构造函数
     *
     * @param array $route       路由配置数组（引用）
     * @param array $namedRoutes 命名路由索引（引用）
     */
    public function __construct(array &$route, array &$namedRoutes)
    {
        $this->route       = &$route;
        $this->namedRoutes = &$namedRoutes;
    }

    /**
     * 为路由命名
     *
     * 通过为路由指定名称，可以在后续通过 Router::urlFor() 反向生成 URL。
     *
     * 用法示例：
     * ```php
     * // 定义命名路由
     * Router::get('/users/{id}', 'UserController@show')->name('user.show');
     *
     * // 反向生成 URL
     * $url = Router::urlFor('user.show', ['id' => 1]);
     * // 返回：/users/1
     * ```
     *
     * @param string $name 路由名称
     *
     * @return self 返回自身实例（支持链式调用）
     *
     * @see    \FLEA\Router::urlFor()
     */
    public function name(string $name): self
    {
        $this->route['name']       = $name;
        $this->namedRoutes[$name]  = &$this->route;
        return $this;
    }
}
