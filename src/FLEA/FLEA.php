<?php

/**
 * FleaPHP 框架入口类
 *
 * FLEA 类作为静态门面（Facade），委托给各专职服务类完成框架初始化。
 * 开发者通过该类访问框架核心功能：配置、容器、缓存、数据库等。
 *
 * 基本用法：
 * ```php
 * // 1. 加载框架
 * require 'vendor/autoload.php';
 *
 * // 2. 加载环境变量
 * \FLEA::loadEnv(__DIR__ . '/../.env');
 *
 * // 3. 加载应用配置
 * \FLEA::loadAppInf(__DIR__ . '/../App/Config.php');
 *
 * // 4. 运行 MVC 应用
 * \FLEA::runMVC();
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 * @see     \FLEA\Config
 * @see     \FLEA\Container
 * @see     \FLEA\Database
 * @see     \FLEA\Cache
 */
class FLEA
{
    /**
     * @var bool 环境变量是否已加载
     */
    private static bool $envLoaded = false;

    /**
     * 加载 .env 环境变量文件
     *
     * 支持多环境文件自动加载：
     * 1. 首先加载基础 `.env` 文件
     * 2. 根据 `APP_ENV` 变量自动加载 `.env.{APP_ENV}` 覆盖配置
     *
     * 用法示例：
     * ```php
     * // 加载 .env，若 APP_ENV=production 则自动加载 .env.production
     * \FLEA::loadEnv(__DIR__ . '/../.env');
     * ```
     *
     * @param string $path .env 文件的完整路径（必须是文件路径，不是目录）
     *
     * @return void
     *
     * @throws \Exception 当 .env 文件不存在时抛出异常
     *
     * @see    \Dotenv\Dotenv::createImmutable()
     */
    public static function loadEnv(string $path): void
    {
        if (self::$envLoaded) return;

        // 1. 验证文件存在
        if (!is_file($path)) {
            throw new \Exception(".env file not found: {$path}");
        }

        // 2. 加载基础 .env
        $dotenv = \Dotenv\Dotenv::createImmutable(dirname($path));
        $dotenv->safeLoad();

        // 3. 读取 APP_ENV，加载对应环境文件
        $appEnv = $_ENV['APP_ENV'] ?? 'local';
        $envFile = dirname($path) . '/.env.' . $appEnv;
        if (is_file($envFile)) {
            $dotenv = \Dotenv\Dotenv::createImmutable(dirname($path), '.env.' . $appEnv);
            $dotenv->safeLoad();  // 覆盖已有变量
        }

        // 4. 设置环境变量（不再定义常量，推荐使用 Env::isProd()）
        // 如需判断环境，使用：\FLEA\Env::isProd() 或 \FLEA\Env::isEnv('production')

        self::$envLoaded = true;
    }

    // =========================================================================
    // 配置管理（委托给 FLEA\Config）
    // =========================================================================

    /**
     * 加载应用配置文件
     *
     * 支持两种调用方式：
     * 1. 传入配置文件路径（字符串）
     * 2. 传入配置数组
     *
     * 用法示例：
     * ```php
     * // 方式 1：传入文件路径
     * \FLEA::loadAppInf(__DIR__ . '/../App/Config.php');
     *
     * // 方式 2：传入配置数组
     * \FLEA::loadAppInf(['dbDSN' => 'mysql://localhost/blog']);
     * ```
     *
     * @param string|array|null $config 配置文件路径或配置数组
     *
     * @return void
     *
     * @throws \FLEA\Exception\ExpectedFile 配置文件路径有效但文件不可读时抛出
     *
     * @see    \FLEA\Config::mergeAppInf()
     */
    public static function loadAppInf($config = null): void
    {
        if (is_string($config)) {
            if (!is_readable($config)) {
                throw new \FLEA\Exception\ExpectedFile($config);
            }
            $config = require($config);
        }
        if (is_array($config)) {
            Config::getInstance()->mergeAppInf($config);
        }
    }

    /**
     * 获取应用配置项的值
     *
     * 用法示例：
     * ```php
     * // 获取单个配置项
     * $controller = \FLEA::getAppInf('defaultController');
     *
     * // 带默认值
     * $timezone = \FLEA::getAppInf('defaultTimezone', 'UTC');
     * ```
     *
     * @param string $option  配置项名称
     * @param mixed  $default 默认值（配置项不存在时返回）
     *
     * @return mixed 配置项的值，不存在时返回默认值
     *
     * @see    \FLEA\Config::getAppInf()
     */
    public static function getAppInf(string $option, $default = null)
    {
        return Config::getInstance()->getAppInf($option, $default);
    }

    /**
     * 获取嵌套配置项的值
     *
     * 用于获取二维数组形式的配置项中的子值。
     *
     * 用法示例：
     * ```php
     * // 获取 dbDSN 配置中的 'host' 值
     * $host = \FLEA::getAppInfValue('dbDSN', 'host');
     * ```
     *
     * @param string $option   配置项名称（如 'dbDSN'）
     * @param string $keyname  子键名称（如 'host'）
     * @param mixed  $default  默认值
     *
     * @return mixed 配置子项的值
     *
     * @see    \FLEA\Config::getAppInfValue()
     */
    public static function getAppInfValue(string $option, string $keyname, $default = null)
    {
        return Config::getInstance()->getAppInfValue($option, $keyname, $default);
    }

    /**
     * 设置配置项的值
     *
     * 用法示例：
     * ```php
     * \FLEA::setAppInfValue('dbDSN', 'host', '192.168.1.100');
     * ```
     *
     * @param string $option   配置项名称
     * @param string $keyname  子键名称
     * @param mixed  $value    要设置的值
     *
     * @return void
     *
     * @see    \FLEA\Config::setAppInfValue()
     */
    public static function setAppInfValue(string $option, string $keyname, $value): void
    {
        Config::getInstance()->setAppInfValue($option, $keyname, $value);
    }

    /**
     * 批量设置配置项
     *
     * 用法示例：
     * ```php
     * // 方式 1：传入键值对数组
     * \FLEA::setAppInf(['key1' => 'value1', 'key2' => 'value2']);
     *
     * // 方式 2：传入单个配置项
     * \FLEA::setAppInf('key1', 'value1');
     * ```
     *
     * @param string|array $option 配置项名称或配置数组
     * @param mixed        $data   配置值（当 $option 为字符串时有效）
     *
     * @return void
     *
     * @see    \FLEA\Config::setAppInf()
     */
    public static function setAppInf($option, $data = null): void
    {
        Config::getInstance()->setAppInf($option, $data);
    }

    // =========================================================================
    // 对象容器（委托给 FLEA\Container，PSR-11 兼容）
    // =========================================================================

    /**
     * 获取单例对象
     *
     * 如果对象尚未注册，则自动创建并缓存。
     * 后续调用将返回同一实例。
     *
     * 用法示例：
     * ```php
     * $logger = \FLEA::getSingleton(\FLEA\Log::class);
     * ```
     *
     * @param string $className 类名
     *
     * @return object 单例对象实例
     *
     * @see    \FLEA\Container::singleton()
     */
    public static function getSingleton(string $className): object
    {
        return Container::getInstance()->singleton($className);
    }

    /**
     * 注册对象到容器
     *
     * 用法示例：
     * ```php
     * // 注册对象实例
     * $obj = new MyClass();
     * \FLEA::register($obj, 'myObject');
     *
     * // 匿名注册（自动命名）
     * \FLEA::register(new MyService());
     * ```
     *
     * @param object     $obj  要注册的对象实例
     * @param string|null $name 对象名称（可选，省略时自动生成）
     *
     * @return object 返回注册的对象实例
     *
     * @see    \FLEA\Container::register()
     */
    public static function register(object $obj, ?string $name = null): object
    {
        return Container::getInstance()->register($obj, $name);
    }

    /**
     * 获取已注册的对象
     *
     * 用法示例：
     * ```php
     * // 获取指定对象
     * $obj = \FLEA::registry('myObject');
     *
     * // 获取所有已注册对象
     * $all = \FLEA::registry();
     * ```
     *
     * @param string|null $name 对象名称（省略时返回所有对象）
     *
     * @return mixed 当 $name 有值时返回对应对象，否则返回所有对象的数组
     *
     * @see    \FLEA\Container::get()
     * @see    \FLEA\Container::all()
     */
    public static function registry(?string $name = null)
    {
        if (is_null($name)) {
            return Container::getInstance()->all();
        }
        return Container::getInstance()->get($name);
    }

    /**
     * 检查对象是否已注册到容器
     *
     * 用法示例：
     * ```php
     * if (\FLEA::isRegistered('myObject')) {
     *     $obj = \FLEA::registry('myObject');
     * }
     * ```
     *
     * @param string $name 对象名称
     *
     * @return bool 对象已注册返回 true，否则返回 false
     *
     * @see    \FLEA\Container::has()
     */
    public static function isRegistered(string $name): bool
    {
        return Container::getInstance()->has($name);
    }

    // =========================================================================
    // 缓存服务（委托给 PSR-16 Cache）
    // =========================================================================

    /**
     * 从缓存获取数据
     *
     * 用法示例：
     * ```php
     * // 获取缓存数据
     * $data = \FLEA::getCache('user_123');
     *
     * // 指定缓存有效期（秒）
     * $data = \FLEA::getCache('user_123', 3600);
     * ```
     *
     * @param string $cacheId        缓存 ID
     * @param int    $time           缓存有效期（秒），默认 900 秒
     * @param bool   $timeIsLifetime 时间参数是否为缓存生命周期（预留参数）
     * @param bool   $cacheIdIsFilename 缓存 ID 是否为文件路径（预留参数）
     *
     * @return mixed 缓存的数据，缓存不存在时返回 false
     *
     * @see    \FLEA\Cache::provider()->get()
     */
    public static function getCache(string $cacheId, int $time = 900, bool $timeIsLifetime = true, bool $cacheIdIsFilename = false)
    {
        return Cache::provider()->get($cacheId) ?? false;
    }

    /**
     * 写入缓存
     *
     * 用法示例：
     * ```php
     * \FLEA::writeCache('user_123', $userData);
     * ```
     *
     * @param string $cacheId           缓存 ID
     * @param mixed  $data              要缓存的数据
     * @param bool   $cacheIdIsFilename 缓存 ID 是否为文件路径（预留参数）
     *
     * @return bool 写入成功返回 true，失败返回 false
     *
     * @see    \FLEA\Cache::provider()->set()
     */
    public static function writeCache(string $cacheId, $data, bool $cacheIdIsFilename = false): bool
    {
        return Cache::provider()->set($cacheId, $data);
    }

    /**
     * 删除缓存
     *
     * 用法示例：
     * ```php
     * \FLEA::purgeCache('user_123');
     * ```
     *
     * @param string $cacheId           缓存 ID
     * @param bool   $cacheIdIsFilename 缓存 ID 是否为文件路径（预留参数）
     *
     * @return bool 删除成功返回 true，失败返回 false
     *
     * @see    \FLEA\Cache::provider()->delete()
     */
    public static function purgeCache(string $cacheId, bool $cacheIdIsFilename = false): bool
    {
        return Cache::provider()->delete($cacheId);
    }

    // =========================================================================
    // 数据库服务（委托给 FLEA\Database）
    // =========================================================================

    /**
     * 获取数据库连接对象（DBO）
     *
     * 用法示例：
     * ```php
     * // 获取默认连接
     * $dbo = \FLEA::getDBO();
     *
     * // 获取指定 DSN 的连接
     * $dbo = \FLEA::getDBO(1);
     * ```
     *
     * @param int|string $dsn DSN 索引或 DSN 字符串（默认 0 表示默认连接）
     *
     * @return \FLEA\Db\Driver\AbstractDriver 数据库驱动对象
     *
     * @see    \FLEA\Database::getInstance()->connect()
     */
    public static function getDBO($dsn = 0): \FLEA\Db\Driver\AbstractDriver
    {
        return Database::getInstance()->connect($dsn);
    }

    /**
     * 解析 DSN 字符串为数组
     *
     * 用法示例：
     * ```php
     * $config = \FLEA::parseDSN('mysql://root:pass@localhost/blog');
     * // 返回：['driver'=>'mysql', 'host'=>'localhost', 'login'=>'root', ...]
     * ```
     *
     * @param string|array $dsn DSN 字符串或数组
     *
     * @return array|null DSN 配置数组，解析失败返回 null
     *
     * @see    \FLEA\Database::getInstance()->parseDSN()
     */
    public static function parseDSN($dsn): ?array
    {
        return Database::getInstance()->parseDSN($dsn);
    }

    // =========================================================================
    // 中间件管理
    // =========================================================================
    // 中间件管理
    // =========================================================================

    /**
     * @var \FLEA\Middleware\MiddlewareInterface[] 已注册的全局中间件
     */
    private static array $middlewares = [];

    /**
     * 注册全局中间件
     *
     * 全局中间件会在所有请求执行前被调用。
     *
     * 用法示例：
     * ```php
     * \FLEA::middleware(new CorsMiddleware());
     * \FLEA::middleware(new AuthMiddleware());
     * ```
     *
     * @param \FLEA\Middleware\MiddlewareInterface $middleware 中间件实例
     *
     * @return void
     *
     * @see    \FLEA\Middleware\MiddlewareInterface
     */
    public static function middleware(\FLEA\Middleware\MiddlewareInterface $middleware): void
    {
        self::$middlewares[] = $middleware;
    }

    // =========================================================================
    // MVC 启动
    // =========================================================================

    /**
     * 绑定 Context 到容器
     *
     * 根据配置自动创建 Context 实例并绑定到容器。
     * 支持多种驱动（Session/Redis/File）和身份标识（Session/JWT/API Key/Request ID）。
     *
     * @return void
     */
    private static function bindContextToContainer(): void
    {
        $driverName = self::getAppInf('contextDriver') ?: 'session';
        $identityName = self::getAppInf('contextIdentity') ?: 'session';

        // 创建驱动实例
        switch ($driverName) {
            case 'redis':
                $driver = new \FLEA\Context\Driver\RedisDriver(
                    self::getAppInf('context.redis') ?? []
                );
                break;
            case 'file':
                $driver = new \FLEA\Context\Driver\FileDriver(
                    self::getAppInf('context.file.path') ?? ''
                );
                break;
            case 'database':
                $driver = new \FLEA\Context\Driver\DatabaseSessionDriver(
                    self::getAppInf('context.database') ?? []
                );
                break;
            case 'session':
            default:
                $driver = new \FLEA\Context\Driver\SessionDriver();
        }

        // 创建身份标识实例
        switch ($identityName) {
            case 'jwt':
                $identity = new \FLEA\Context\Identity\JwtIdentity(
                    self::getAppInf('jwt.secret') ?? ''
                );
                break;
            case 'api-key':
                $identity = new \FLEA\Context\Identity\ApiKeyIdentity(
                    self::getAppInf('context.apiKey.header') ?? 'X-API-Key'
                );
                break;
            case 'request-id':
                $identity = new \FLEA\Context\Identity\RequestIdIdentity(
                    self::getAppInf('context.requestId.header') ?? 'X-Request-ID'
                );
                break;
            case 'session':
            default:
                $identity = new \FLEA\Context\Identity\SessionIdentity();
        }

        // 创建 Context 实例并绑定到容器
        $context = new \FLEA\Context\Context($driver, $identity);
        $container = self::getSingleton(\FLEA\Container::class);
        $container->set(\FLEA\Context\Context::class, $context);
    }

    /**
     * 启动 MVC 应用
     *
     * 这是框架的入口方法，完成以下工作：
     * 1. 初始化框架环境（时区、异常处理、缓存等）
     * 2. 路由匹配（URL_ROUTER 模式）或解析（URL_STANDARD 模式）
     * 3. 执行中间件管道
     * 4. 调度控制器执行
     *
     * 用法示例：
     * ```php
     * require 'vendor/autoload.php';
     * \FLEA::loadEnv(__DIR__ . '/../.env');
     * \FLEA::loadAppInf(__DIR__ . '/../App/Config.php');
     * \FLEA::runMVC();
     * ```
     *
     * @return void
     *
     * @see    \FLEA::init()
     * @see    \FLEA\Router::dispatch()
     * @see    \FLEA\Dispatcher\Simple::dispatching()
     * @see    \FLEA\Middleware\Pipeline
     */
    public static function runMVC(): void
    {
        self::init();

        $dispatch = function() {
            $dispatcherClass = self::getAppInf('dispatcher');
            if (!class_exists($dispatcherClass, true)) {
                throw new \FLEA\Exception\ExpectedClass($dispatcherClass);
            }
            $dispatcher = new $dispatcherClass($_GET);
            self::register($dispatcher, $dispatcherClass);
            /** @var \FLEA\Dispatcher\Simple $dispatcher */
            $dispatcher->dispatching();
        };

        if (self::getAppInf('urlMode') === URL_ROUTER) {
            // URL_ROUTER 模式：注册默认兜底路由（可通过 routerDefaultRoute=false 关闭）
            if (self::getAppInf('routerDefaultRoute') !== false) {
                // 由 Router 类检查开发者是否已定义过兜底路由，未定义则注册
                \FLEA\Router::registerFallback(
                    self::getAppInf('defaultController'),
                    self::getAppInf('defaultAction')
                );
            }
            // Router 模式：必须匹配，未匹配直接 404
            if (!\FLEA\Router::dispatch()) {
                \FLEA\Response::error('Not Found', 404);
            }
        } else {
            // URL_STANDARD：尝试 Router 匹配，未匹配降级旧式路由
            \FLEA\Router::dispatch();
        }

        // 全局中间件 + 路由级中间件
        $all = array_merge(self::$middlewares, \FLEA\Router::getMatchedMiddlewares());

        if (empty($all)) {
            $dispatch();
        } else {
            $pipeline = \FLEA\Middleware\Pipeline::create();
            foreach ($all as $mw) {
                $pipeline->pipe($mw);
            }
            $pipeline->run($dispatch);
        }
    }

    /**
     * 初始化框架环境
     *
     * 完成以下初始化工作：
     * - 设置时区
     * - 注册异常处理器
     * - 初始化缓存目录
     * - 加载请求过滤脚本
     * - 加载自动加载脚本
     * - 初始化 Session
     * - 设置响应头
     * - 输出日志 Trace ID
     *
     * 该方法会被 `runMVC()` 自动调用，一般无需手动调用。
     *
     * @param bool $loadMVC 是否启动 MVC 模式（预留参数）
     *
     * @return void
     *
     * @see    \FLEA::runMVC()
     */
    public static function init(bool $loadMVC = false): void
    {
        static $initialized = false;
        if ($initialized) { return; }
        $initialized = true;

        // 时区
        $timezone = self::getAppInf('defaultTimezone') ?: ini_get('date.timezone') ?: 'Asia/Shanghai';
        date_default_timezone_set($timezone);

        // 异常处理
        set_exception_handler(self::getAppInf('exceptionHandler'));

        // 缓存目录
        if (!self::getAppInf('internalCacheDir')) {
            self::setAppInf('internalCacheDir', __DIR__ . '/_Cache');
        }

        foreach ((array)self::getAppInf('requestFilters') as $file) {
            if (file_exists($file)) { require_once($file); }
        }
        foreach ((array)self::getAppInf('autoLoad') as $file) {
            if (file_exists($file)) { require_once($file); }
        }

        // 自动绑定 Context 到容器（如果配置了 contextDriver）
        self::bindContextToContainer();

        define('RESPONSE_CHARSET', self::getAppInf('responseCharset'));
        define('DATABASE_CHARSET', self::getAppInf('databaseCharset'));

        if (self::getAppInf('autoResponseHeader')) {
            header('Content-Type: text/html; charset=' . self::getAppInf('responseCharset'));
        }

        // 输出 traceId 响应头（在任何响应体之前）
        if (self::getAppInf('logEnabled')) {
            $log = self::getSingleton(\FLEA\Log::class);
            header('X-Trace-Id: ' . $log->getTraceId());
        }
    }
}
