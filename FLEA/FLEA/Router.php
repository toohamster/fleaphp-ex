<?php

namespace FLEA;

/**
 * HTTP 路由器（支持路由级中间件）
 *
 * 用法：
 *   Router::get('/users', 'UserController@index');
 *   Router::post('/users', 'UserController@store', [new AuthMiddleware()]);
 *   Router::get('/users/{id:\d+}', 'UserController@show');
 *
 *   Router::group('/admin', function() {
 *       Router::get('/stats', 'AdminController@stats');
 *   }, [new AuthMiddleware(), new RateLimitMiddleware()]);
 *
 *   Router::dispatch();
 *   FLEA::runMVC();
 *
 */
class Router
{
    /** @var array[] 已注册的路由 */
    private static array $routes = [];

    /** @var array<string, array> 命名路由索引 name => route */
    private static array $namedRoutes = [];

    /** @var string 当前分组前缀 */
    private static string $prefix = '';

    /** @var \FLEA\Middleware\MiddlewareInterface[] 当前分组中间件 */
    private static array $groupMiddlewares = [];

    /** @var \FLEA\Middleware\MiddlewareInterface[] 当前请求匹配到的路由中间件 */
    private static array $matchedMiddlewares = [];

    // -------------------------------------------------------------------------
    // 路由注册
    // -------------------------------------------------------------------------

    public static function get(string $path, string $handler, array $middlewares = []): \FLEA\Route
    {
        return self::add('GET', $path, $handler, $middlewares);
    }

    public static function post(string $path, string $handler, array $middlewares = []): \FLEA\Route
    {
        return self::add('POST', $path, $handler, $middlewares);
    }

    public static function put(string $path, string $handler, array $middlewares = []): \FLEA\Route
    {
        return self::add('PUT', $path, $handler, $middlewares);
    }

    public static function patch(string $path, string $handler, array $middlewares = []): \FLEA\Route
    {
        return self::add('PATCH', $path, $handler, $middlewares);
    }

    public static function delete(string $path, string $handler, array $middlewares = []): \FLEA\Route
    {
        return self::add('DELETE', $path, $handler, $middlewares);
    }

    public static function any(string $path, string $handler, array $middlewares = []): \FLEA\Route
    {
        $route = null;
        foreach (['GET','POST','PUT','PATCH','DELETE'] as $method) {
            $route = self::add($method, $path, $handler, $middlewares);
        }
        return $route;
    }

    /**
     * @param array $middlewares 该分组所有路由共享的中间件
     */
    public static function group(string $prefix, callable $callback, array $middlewares = []): void
    {
        $prevPrefix      = self::$prefix;
        $prevMiddlewares = self::$groupMiddlewares;

        self::$prefix           = $prevPrefix . '/' . trim($prefix, '/');
        self::$groupMiddlewares = array_merge($prevMiddlewares, $middlewares);

        $callback();

        self::$prefix           = $prevPrefix;
        self::$groupMiddlewares = $prevMiddlewares;
    }

    private static function add(string $method, string $path, string $handler, array $middlewares): \FLEA\Route
    {
        $path = self::$prefix . '/' . ltrim($path, '/');
        $path = '/' . trim($path, '/') ?: '/';
        $middlewares = array_merge(self::$groupMiddlewares, $middlewares);
        $route = ['method' => $method, 'path' => $path, 'handler' => $handler, 'middlewares' => $middlewares, 'name' => null];
        self::$routes[] = &$route;
        return new \FLEA\Route($route, self::$namedRoutes);
    }

    /**
     * 根据命名路由生成 URL
     *
     * @param string $name 路由名称
     * @param array $params 路径参数
     * @throws \InvalidArgumentException
     */
    public static function urlFor(string $name, array $params = []): string
    {
        if (!isset(self::$namedRoutes[$name])) {
            throw new \InvalidArgumentException("Named route '{$name}' not found.");
        }
        $path = self::$namedRoutes[$name]['path'];
        $path = preg_replace_callback('/\{(\w+)(?::[^}]+)?\}/', function($m) use ($params, $name) {
            $key = $m[1];
            if (!isset($params[$key])) {
                throw new \InvalidArgumentException("Missing parameter '{$key}' for route '{$name}'.");
            }
            return urlencode($params[$key]);
        }, $path);

        // 无 rewrite 环境：配置了 urlScriptName 则加前缀
        $scriptName = \FLEA::getAppInf('urlScriptName') ?? '';
        return $scriptName !== '' ? $scriptName . $path : $path;
    }

    // -------------------------------------------------------------------------
    // 路由匹配与分发
    // -------------------------------------------------------------------------

    /**
     * 匹配当前请求，命中则注入 $_GET 并返回 true，否则返回 false
     * 匹配到的路由中间件可通过 Router::getMatchedMiddlewares() 获取
     */
    public static function dispatch(): bool
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        $uri = self::resolveUri();

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) { continue; }

            $params = self::match($route['path'], $uri);
            if ($params === null) { continue; }

            $handler = $route['handler'];
            // 替换 handler 中的占位符
            foreach ($params as $key => $value) {
                $handler = str_replace('{' . $key . '}', $value, $handler);
            }

            [$controller, $action] = explode('@', $handler) + [1 => 'index'];

            $_GET[\FLEA::getAppInf('controllerAccessor')] = $controller;
            $_GET[\FLEA::getAppInf('actionAccessor')]     = $action;
            $_GET = array_merge($_GET, $params);
            $_REQUEST = array_merge($_REQUEST, $_GET);

            self::$matchedMiddlewares = $route['middlewares'];
            return true;
        }

        return false;
    }

    /**
     * 解析当前请求的路径
     * 自动兼容 URL_REWRITE、URL_PATHINFO、URL_STANDARD 三种模式
     */
    private static function resolveUri(): string
    {
        // 优先从 PATH_INFO 取（无 rewrite 环境），其次 REQUEST_URI
        $scriptName = \FLEA::getAppInf('urlScriptName') ?? '';
        if ($scriptName !== '') {
            // 配置了入口文件名，说明是 pathinfo 环境，从 PATH_INFO 取
            $path = $_SERVER['PATH_INFO'] ?? $_SERVER['ORIG_PATH_INFO'] ?? '/';
        } else {
            // rewrite 环境，从 REQUEST_URI 取
            $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        }

        return '/' . trim($path, '/') ?: '/';
    }

    /**
     * 返回当前请求匹配到的路由级中间件
     *
     * @return \FLEA\Middleware\MiddlewareInterface[]
     */
    public static function getMatchedMiddlewares(): array
    {
        return self::$matchedMiddlewares;
    }

    private static function match(string $pattern, string $uri): ?array
    {
        $regex = preg_replace_callback('/\{(\w+)(?::([^}]+))?\}/', function($m) {
            return '(?P<' . $m[1] . '>' . ($m[2] ?? '[^/]+') . ')';
        }, $pattern);

        if (!preg_match('#^' . $regex . '$#', $uri, $matches)) {
            return null;
        }

        return array_filter($matches, fn($k) => is_string($k), ARRAY_FILTER_USE_KEY);
    }

    public static function routes(): array
    {
        return self::$routes;
    }
}
