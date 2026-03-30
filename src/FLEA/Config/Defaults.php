<?php

namespace FLEA\Config;

/**
 * FLEA 框架默认配置
 *
 * 包含框架运行所需的所有默认配置项
 * 应用配置会覆盖这些默认值
 * 环境变量 (.env) 会覆盖应用配置
 *
 * @package FLEA
 * @subpackage Config
 * @author toohamster
 * @version 2.0.0
 */
class Defaults
{
    /**
     * 获取默认配置
     *
     * 配置项说明：
     * - defaultTimezone: 默认时区
     * - databaseCharset: 数据库字符集
     * - dbDSN: 数据库连接配置（支持环境变量）
     * - defaultController: 默认控制器名
     * - defaultAction: 默认动作名
     * - view: 视图类
     * - cacheProvider: 缓存提供者类
     * - sessionProvider: Session 提供者类
     * - logEnabled: 是否启用日志
     * - jwtSecret: JWT 密钥（支持环境变量）
     * - jwtTtl: JWT 有效期（秒）
     *
     * @return array 默认配置数组
     */
    public static function getConfig(): array
    {
        return [
            // 时区
            'defaultTimezone' => 'Asia/Shanghai',

            // 数据库
            'databaseCharset' => 'utf8mb4',
            'dbDSN' => [
                'driver' => env('DB_DRIVER', 'mysql'),
                'host' => env('DB_HOST', '127.0.0.1'),
                'port' => env('DB_PORT', '3306'),
                'login' => env('DB_USERNAME', 'root'),
                'password' => env('DB_PASSWORD', ''),
                'database' => env('DB_DATABASE', ''),
                'charset' => 'utf8mb4',
            ],

            // 控制器
            'defaultController' => 'Index',
            'defaultAction' => 'index',
            'controllerClassPrefix' => 'App\\Controller\\',
            'controllerFileSuffix' => 'Controller.php',
            'controllerDir' => 'App/Controller',

            // URL 模式
            'urlScriptName' => '',
            'urlLowerChar' => false,
            'urlCaseInsensitive' => true,  // 路由匹配大小写不敏感（默认开启）

            // 视图
            'view' => \FLEA\View\Simple::class,
            'viewConfig' => [
                'templateDir' => 'App/View',
                'cacheDir' => 'cache',
                'cacheLifeTime' => 900,
                'enableCache' => false,
            ],

            // 缓存
            'cacheProvider' => \FLEA\Cache\FileCache::class,
            'internalCacheDir' => env('CACHE_DIR', 'cache'),

            // Session
            'sessionProvider' => null,
            'autoSessionStart' => false,
            'sessionName' => 'FLEA_SESSION',
            'sessionLifetime' => (int) env('SESSION_LIFETIME', 120),

            // Context（请求上下文存储）
            'contextDriver' => env('CONTEXT_DRIVER', 'session'),  // session/redis/file/database
            'contextIdentity' => env('CONTEXT_IDENTITY', 'session'),  // session/jwt/api-key/request-id
            'context' => [
                'redis' => [
                    'host' => env('CONTEXT_REDIS_HOST', '127.0.0.1'),
                    'port' => (int) env('CONTEXT_REDIS_PORT', 6379),
                    'password' => env('CONTEXT_REDIS_PASSWORD', ''),
                    'prefix' => env('CONTEXT_REDIS_PREFIX', 'fleaphp:context:'),
                ],
                'file' => [
                    'path' => env('CONTEXT_FILE_PATH', ''),
                ],
                'database' => [
                    'tableName' => env('CONTEXT_DB_TABLE', 'contexts'),
                    'fieldId' => env('CONTEXT_DB_FIELD_ID', 'context_id'),
                    'fieldData' => env('CONTEXT_DB_FIELD_DATA', 'context_data'),
                    'fieldActivity' => env('CONTEXT_DB_FIELD_ACTIVITY', 'activity'),
                    'lifeTime' => (int) env('CONTEXT_DB_LIFETIME', 3600),
                ],
                'apiKey' => [
                    'header' => env('CONTEXT_API_KEY_HEADER', 'X-API-Key'),
                ],
                'requestId' => [
                    'header' => env('CONTEXT_REQUEST_ID_HEADER', 'X-Request-ID'),
                ],
            ],

            // 日志
            'logEnabled' => env('LOG_ENABLED', false),
            'logProvider' => null,
            'logFilename' => env('LOG_FILENAME', 'app.log'),
            'logLevel' => env('LOG_LEVEL', 'debug'),

            // 错误处理
            'exceptionHandler' => '__FLEA_EXCEPTION_HANDLER',
            'displayErrors' => env('APP_DEBUG', false),
            'friendlyErrorsMessage' => false,
            'displaySource' => false,
            'errorMessagesFile' => '',

            // 响应
            'responseCharset' => 'UTF-8',
            'autoResponseHeader' => true,
            'forceJsonResponse' => false,

            // 调度器
            'dispatcher' => \FLEA\Dispatcher\Simple::class,

            // 中间件
            'middlewares' => [],

            // 请求过滤
            'requestFilters' => [],

            // 自动加载
            'autoLoad' => [],

            // JWT
            'jwtSecret' => env('JWT_SECRET', ''),
            'jwtTtl' => (int) env('JWT_TTL', 7200),
        ];
    }
}
