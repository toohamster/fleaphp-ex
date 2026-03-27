<?php

namespace FLEA;

/**
 * 单条路由，支持链式命名
 *
 */
class Route
{
    private array $route;
    private array $namedRoutes;

    public function __construct(array &$route, array &$namedRoutes)
    {
        $this->route       = &$route;
        $this->namedRoutes = &$namedRoutes;
    }

    /**
     * 为路由命名，用于反向生成 URL
     *
     * Router::get('/users/{id}', 'UserController@show')->name('user.show');
     * Router::urlFor('user.show', ['id' => 1]); // → /users/1
     */
    public function name(string $name): self
    {
        $this->route['name']       = $name;
        $this->namedRoutes[$name]  = &$this->route;
        return $this;
    }
}
