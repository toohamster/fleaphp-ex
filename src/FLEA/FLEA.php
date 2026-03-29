<?php

/**
 * FleaPHP 框架入口
 *
 * require('FLEA.php') 即可完成框架初始化。
 * FLEA 类作为静态门面，委托给各专职服务类。
 */

define('FLEA_LOADED_TIME', microtime());
define('FLEA_VERSION', '2.0.0');
define('DS', DIRECTORY_SEPARATOR);
define('URL_STANDARD', 'URL_STANDARD');
define('URL_ROUTER',   'URL_ROUTER');
define('RBAC_EVERYONE', 'RBAC_EVERYONE');
define('RBAC_HAS_ROLE', 'RBAC_HAS_ROLE');
define('RBAC_NO_ROLE', 'RBAC_NO_ROLE');
define('RBAC_NULL', 'RBAC_NULL');
define('ACTION_ALL', 'ACTION_ALL');

define('FLEA_DIR', __DIR__ . '/FLEA');

use FLEA\Config;
use FLEA\Container;
use FLEA\Database;
use FLEA\Cache;

class FLEA
{
    private static bool $envLoaded = false;

    /**
     * 加载 .env 环境变量文件
     *
     * 用法：\FLEA::loadEnv(__DIR__ . '/../.env');
     *
     * 支持多环境文件：
     * - 加载基础 .env 后，根据 APP_ENV 自动加载 .env.{APP_ENV}
     * - 例如：APP_ENV=production 时，自动加载 .env.production 覆盖
     *
     * @param string $path .env 文件的完整路径
     * @return void
     * @throws \Exception 当 .env 文件不存在时抛出异常
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
    // 配置

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

    public static function getAppInf(string $option, $default = null)
    {
        return Config::getInstance()->getAppInf($option, $default);
    }

    public static function getAppInfValue(string $option, string $keyname, $default = null)
    {
        return Config::getInstance()->getAppInfValue($option, $keyname, $default);
    }

    public static function setAppInfValue(string $option, string $keyname, $value): void
    {
        Config::getInstance()->setAppInfValue($option, $keyname, $value);
    }

    public static function setAppInf($option, $data = null): void
    {
        Config::getInstance()->setAppInf($option, $data);
    }

    // 对象容器

    public static function getSingleton(string $className): object
    {
        return Container::getInstance()->singleton($className);
    }

    public static function register(object $obj, ?string $name = null): object
    {
        return Container::getInstance()->register($obj, $name);
    }

    public static function registry(?string $name = null)
    {
        if (is_null($name)) {
            return Container::getInstance()->all();
        }
        return Container::getInstance()->get($name);
    }

    public static function isRegistered(string $name): bool
    {
        return Container::getInstance()->has($name);
    }

    // 缓存（委托 PSR-16 Cache）

    public static function getCache(string $cacheId, int $time = 900, bool $timeIsLifetime = true, bool $cacheIdIsFilename = false)
    {
        return Cache::provider()->get($cacheId) ?? false;
    }

    public static function writeCache(string $cacheId, $data, bool $cacheIdIsFilename = false): bool
    {
        return Cache::provider()->set($cacheId, $data);
    }

    public static function purgeCache(string $cacheId, bool $cacheIdIsFilename = false): bool
    {
        return Cache::provider()->delete($cacheId);
    }

    // 数据库

    public static function getDBO($dsn = 0): \FLEA\Db\Driver\AbstractDriver
    {
        return Database::getInstance()->connect($dsn);
    }

    public static function parseDSN($dsn): ?array
    {
        return Database::getInstance()->parseDSN($dsn);
    }

    // 辅助

    public static function loadHelper(string $helperName): void
    {
        $setting = self::getAppInf('helper.' . strtolower($helperName));
        if (!$setting) {
            throw new \FLEA\Exception\NotExistsKeyName('helper.' . $helperName);
        }
        if (!class_exists($setting, true)) {
            throw new \FLEA\Exception\ExpectedClass($setting);
        }
    }

    // 中间件

    /** @var \FLEA\Middleware\MiddlewareInterface[] */
    private static array $middlewares = [];

    public static function middleware(\FLEA\Middleware\MiddlewareInterface $middleware): void
    {
        self::$middlewares[] = $middleware;
    }

    // MVC 启动

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

        if (self::getAppInf('sessionProvider')) {
            self::getSingleton(self::getAppInf('sessionProvider'));
        }
        if (self::getAppInf('autoSessionStart')) {
            session_start();
        }

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
