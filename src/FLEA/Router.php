<?php

namespace FLEA;

/**
 * HTTP 路由器（支持路由级中间件）
 *
 * 提供 RESTful 路由、路由分组、命名路由、中间件支持等功能。
 *
 * 支持的路由方法：
 * - get/post/put/patch/delete：注册指定 HTTP 方法的路由
 * - any：注册所有 HTTP 方法的路由
 * - group：路由分组，支持前缀和共享中间件
 *
 * 用法示例：
 * ```php
 * // 基础路由
 * Router::get('/users', 'UserController@index');
 * Router::post('/users', 'UserController@store', [new AuthMiddleware()]);
 * Router::get('/users/{id:\d+}', 'UserController@show');
 *
 * // 路由分组
 * Router::group('/admin', function() {
 *     Router::get('/stats', 'AdminController@stats');
 *     Router::get('/users', 'AdminController@users');
 * }, [new AuthMiddleware(), new RateLimitMiddleware()]);
 *
 * // 命名路由
 * Router::get('/users/{id}', 'UserController@show')->name('user.show');
 * $url = Router::urlFor('user.show', ['id' => 123]);
 *
 * // 分发路由
 * Router::dispatch();
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 * @see     \FLEA\Route
 * @see     \FLEA\Middleware\MiddlewareInterface
 */
class Router
{
    /**
     * @var string Controller 参数键名
     */
    public const CONTROLLER_KEY = 'controller';

    /**
     * @var string Action 参数键名
     */
    public const ACTION_KEY = 'action';

    /**
     * @var array[] 已注册的路由数组
     */
    private static array $routes = [];

    /**
     * @var array<string, array> 命名路由索引（name => route）
     */
    private static array $namedRoutes = [];

    /**
     * @var string 当前路由分组前缀
     */
    private static string $prefix = '';

    /**
     * @var \FLEA\Middleware\MiddlewareInterface[] 当前分组中间件栈
     */
    private static array $groupMiddlewares = [];

    /**
     * @var \FLEA\Middleware\MiddlewareInterface[] 当前请求匹配到的路由中间件
     */
    private static array $matchedMiddlewares = [];

    // -------------------------------------------------------------------------
    // 路由注册
    // -------------------------------------------------------------------------

    /**
     * 注册 GET 路由
     *
     * 用法示例：
     * ```php
     * Router::get('/users', 'UserController@index');
     * Router::get('/users/{id}', 'UserController@show')->name('user.show');
     * ```
     *
     * @param string $path        路由路径（支持参数如 {id:\d+}）
     * @param string $handler     处理器（格式：Controller@action）
     * @param array  $middlewares 中间件数组
     *
     * @return \FLEA\Route Route 对象（可用于链式调用 name()）
     */
    public static function get(string $path, string $handler, array $middlewares = []): \FLEA\Route
    {
        return self::add('GET', $path, $handler, $middlewares);
    }

    /**
     * 注册 POST 路由
     *
     * 用法示例：
     * ```php
     * Router::post('/users', 'UserController@store', [new AuthMiddleware()]);
     * ```
     *
     * @param string $path        路由路径
     * @param string $handler     处理器
     * @param array  $middlewares 中间件数组
     *
     * @return \FLEA\Route Route 对象
     */
    public static function post(string $path, string $handler, array $middlewares = []): \FLEA\Route
    {
        return self::add('POST', $path, $handler, $middlewares);
    }

    /**
     * 注册 PUT 路由
     *
     * @param string $path        路由路径
     * @param string $handler     处理器
     * @param array  $middlewares 中间件数组
     *
     * @return \FLEA\Route Route 对象
     */
    public static function put(string $path, string $handler, array $middlewares = []): \FLEA\Route
    {
        return self::add('PUT', $path, $handler, $middlewares);
    }

    /**
     * 注册 PATCH 路由
     *
     * @param string $path        路由路径
     * @param string $handler     处理器
     * @param array  $middlewares 中间件数组
     *
     * @return \FLEA\Route Route 对象
     */
    public static function patch(string $path, string $handler, array $middlewares = []): \FLEA\Route
    {
        return self::add('PATCH', $path, $handler, $middlewares);
    }

    /**
     * 注册 DELETE 路由
     *
     * @param string $path        路由路径
     * @param string $handler     处理器
     * @param array  $middlewares 中间件数组
     *
     * @return \FLEA\Route Route 对象
     */
    public static function delete(string $path, string $handler, array $middlewares = []): \FLEA\Route
    {
        return self::add('DELETE', $path, $handler, $middlewares);
    }

    /**
     * 注册任意 HTTP 方法的路由（GET/POST/PUT/PATCH/DELETE）
     *
     * @param string $path        路由路径
     * @param string $handler     处理器
     * @param array  $middlewares 中间件数组
     *
     * @return \FLEA\Route Route 对象
     */
    public static function any(string $path, string $handler, array $middlewares = []): \FLEA\Route
    {
        $route = null;
        foreach (['GET','POST','PUT','PATCH','DELETE'] as $method) {
            $route = self::add($method, $path, $handler, $middlewares);
        }
        return $route;
    }

    /**
     * 注册路由分组
     *
     * 分组内的所有路由会共享相同的路径前缀和中间件。
     *
     * 用法示例：
     * ```php
     * Router::group('/admin', function() {
     *     Router::get('/stats', 'AdminController@stats');
     *     Router::get('/users', 'AdminController@users');
     * }, [new AuthMiddleware()]);
     * ```
     *
     * @param string   $prefix      路由分组前缀
     * @param callable $callback    分组路由定义回调函数
     * @param array    $middlewares 该分组所有路由共享的中间件
     *
     * @return void
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

    /**
     * 添加路由到路由表
     *
     * @param string $method      HTTP 方法
     * @param string $path        路由路径
     * @param string $handler     处理器
     * @param array  $middlewares 中间件数组
     *
     * @return \FLEA\Route Route 对象
     */
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
     * 将命名路由的路径模板中的参数替换为实际值，生成完整 URL。
     *
     * 用法示例：
     * ```php
     * // 定义命名路由
     * Router::get('/users/{id}', 'UserController@show')->name('user.show');
     *
     * // 生成 URL
     * $url = Router::urlFor('user.show', ['id' => 123]);
     * // 返回：/users/123
     * ```
     *
     * @param string $name   路由名称
     * @param array  $params 路径参数数组
     *
     * @return string 生成的 URL
     *
     * @throws \InvalidArgumentException 当命名路由不存在或参数缺失时抛出
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

        // 根据 urlScriptName 配置决定是否加入口文件前缀
        $scriptName = \FLEA::getAppInf('urlScriptName') ?? '';
        $url = $scriptName !== '' ? $scriptName . $path : $path;

        // 根据 urlLowerChar 配置决定是否转小写（默认 false，保持路由定义的原样）
        if (\FLEA::getAppInf('urlLowerChar') ?? false) {
            $url = strtolower($url);
        }

        return $url;
    }

    // -------------------------------------------------------------------------
    // 路由匹配与分发
    // -------------------------------------------------------------------------

    /**
     * 匹配当前请求
     *
     * 根据请求方法和 URI 匹配路由表中的路由。命中时将参数注入 $_GET，
     * 并返回 true。匹配到的路由中间件可通过 getMatchedMiddlewares() 获取。
     *
     * 用法示例：
     * ```php
     * if (Router::dispatch()) {
     *     // 路由匹配成功，执行中间件和控制器
     * } else {
     *     // 路由未匹配，返回 404
     * }
     * ```
     *
     * @return bool 请求是否匹配成功
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
            // 替换 handler 中的占位符，支持过滤器语法 {param|filter}
            foreach ($params as $key => $value) {
                // 处理带过滤器的占位符 {key|filter}
                $handler = preg_replace_callback('/\{' . $key . '\|(\w+)\}/', function($m) use ($value) {
                    $filter = $m[1];
                    switch ($filter) {
                        case 'lower':
                            return strtolower($value);
                        case 'upper':
                            return strtoupper($value);
                        case 'ucfirst':
                            return ucfirst($value);
                        case 'lcfirst':
                            return lcfirst($value);
                        default:
                            return $value;
                    }
                }, $handler);
                // 处理不带过滤器的占位符 {key}
                $handler = str_replace('{' . $key . '}', $value, $handler);
            }

            [$controller, $action] = explode('@', $handler) + [1 => 'index'];

            $_GET[self::CONTROLLER_KEY] = $controller;
            $_GET[self::ACTION_KEY]     = $action;
            $_GET = array_merge($_GET, $params);
            $_REQUEST = array_merge($_REQUEST, $_GET);

            self::$matchedMiddlewares = $route['middlewares'];
            return true;
        }

        return false;
    }

    /**
     * 解析当前请求的路径
     *
     * 自动兼容 Rewrite 和 PATHINFO 两种环境。
     * 根据配置的 urlScriptName 判断环境，从 PATH_INFO 或 REQUEST_URI 获取路径。
     *
     * @return string 解析后的 URI 路径
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
     * 获取当前请求匹配到的路由中间件
     *
     * @return \FLEA\Middleware\MiddlewareInterface[] 中间件数组
     */
    public static function getMatchedMiddlewares(): array
    {
        return self::$matchedMiddlewares;
    }

    /**
     * 匹配 URI 与路由模式
     *
     * 将路由路径模式中的 {param} 占位符转换为正则表达式，
     * 匹配 URI 并提取路径参数。
     * 支持大小写不敏感匹配（由 urlCaseInsensitive 配置控制）。
     *
     * @param string $pattern 路由模式（如 /users/{id:\d+}）
     * @param string $uri     请求 URI
     *
     * @return array|null 匹配成功返回参数数组，失败返回 null
     */
    private static function match(string $pattern, string $uri): ?array
    {
        $regex = preg_replace_callback('/\{(\w+)(?::([^}]+))?\}/', function($m) {
            return '(?P<' . $m[1] . '>' . ($m[2] ?? '[^/]+') . ')';
        }, $pattern);

        // 大小写不敏感匹配（默认开启）
        $caseInsensitive = \FLEA::getAppInf('urlCaseInsensitive') ?? true;
        $pattern = $caseInsensitive ? '(?i)' . $regex : $regex;

        if (!preg_match('#^' . $pattern . '$#', $uri, $matches)) {
            return null;
        }

        return array_filter($matches, fn($k) => is_string($k), ARRAY_FILTER_USE_KEY);
    }

    /**
     * 获取所有已注册的路由
     *
     * @return array 路由数组
     */
    public static function routes(): array
    {
        return self::$routes;
    }

    /**
     * 注册兜底路由（如果开发者未定义）
     *
     * 检查路由表中是否已有通配符路由或根路由，
     * 若无则注册默认的兜底路由规则。
     *
     * @param string $defaultController 默认控制器名（不含 Controller 后缀）
     * @param string $defaultAction     默认 Action 名
     *
     * @return void
     */
    public static function registerFallback(string $defaultController, string $defaultAction): void
    {
        $hasControllerAction = false;
        $hasController = false;
        $hasRoot = false;

        foreach (self::$routes as $route) {
            $path = $route['path'];
            if (mb_str_contains($path, '{controller}') && mb_str_contains($path, '{action}')) {
                $hasControllerAction = true;
            }
            if (mb_str_contains($path, '{controller}') && !mb_str_contains($path, '{action}')) {
                $hasController = true;
            }
            if ($path === '/') {
                $hasRoot = true;
            }
        }

        if (!$hasControllerAction) {
            // 兜底路由：controller/action → controllerController@action（URL 小写，action 首字母大写）
            self::any("/{controller}/{action}", "{controller|lower}Controller@{action|ucfirst}")->name('fallback.controller_action');
        }
        if (!$hasController) {
            // 兜底路由：controller → controllerController@index（URL 小写）
            self::any("/{controller}", "{controller|lower}Controller@index")->name('fallback.controller');
        }
        if (!$hasRoot) {
            self::any("/", $defaultController . 'Controller@' . $defaultAction)->name('fallback.root');
        }
    }
}
