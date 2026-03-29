<?php

/**
 * “调试”模式的 FleaPHP 应用程序的默认设置
 */

return [
    // {{{ 核心配置

    /**
     * 应用层序的默认时区设置，仅针对 PHP 5.1 以后版本
     * 如果该设置为 null，则以服务器设置为准。如果服务器没有设置时区，则设置为 Asia/ShangHai。
     */
    'defaultTimezone'           => null,

    /**
     * 默认控制器名
     */
    'defaultController'         => 'Default',

    /**
     * 默认动作方法名
     */
    'defaultAction'             => 'index',

    /**
     * url 参数的传递模式，可以是标准、PATHINFO、URL 重写等模式
     */
    'urlScriptName'             => '',     // 无 rewrite 环境下的入口文件名，如 '/index.php'；有 rewrite 则留空
    'urlMode'                   => URL_STANDARD,

    /**
     * 指示默认的应用程序入口文件名
     */
    'urlBootstrap'              => 'index.php',

    /**
     * 指示在生成 url 时，是否总是使用应用程序入口文件名，仅限 URL_STANDARD 模式
     * 如果该设置为 false，则生成的 url 类似：
     * http://www.example.com/?controller=xxx&action=yyy
     */
    'urlAlwaysUseBootstrap'     => true,

    /**
     * 指示在生成 url 时，是否总是使用完整的控制器名和动作名
     * 如果该设置为 false，则默认的控制器和动作名不会出现在 url 中
     */
    'urlAlwaysUseAccessor'      => true,

    /**
     * 指示在 PATHINFO 和 REWRITE 模式下，用什么符号作为 URL 参数名和参数值的连接字符
     */
    'urlParameterPairStyle'     => '/',

    /**
     * 是否将 url 参数中包含的控制器名字和动作名字强制转为小写
     */
    'urlLowerChar'              => true,

    /**
     * 调用 url() 函数时，要调用的 callback 方法
     */
    'urlCallback'               => null,

    /**
     * 控制器类名称前缀
     */
    'controllerClassPrefix'     => '\\App\\Controller\\',

    /**
     * 控制器中，动作方法名的前缀和后缀
     * 使用前缀和后缀可以进一步保护控制器中的私有方法
     */
    'actionMethodPrefix'        => 'action',
    'actionMethodSuffix'        => '',

    /**
     * 应用程序要使用的 url 调度器
     */
    'dispatcher'                => \FLEA\Dispatcher\Simple::class,

    /**
     * 调度器调度失败（例如控制器或控制器方法不存在）后，要调用的处理程序
     */
    'dispatcherFailedCallback'  => null,

    /**
     * FleaPHP 内部及 cache 系列函数使用的缓存目录
     * 应用程序必须设置该选项才能使用 cache 功能。
     */
    'internalCacheDir'          => null,
    'cacheTtl'                  => null,  // 缓存默认 TTL（秒），null 表示永不过期
    'cacheProvider'             => null,  // 缓存驱动，null 使用文件缓存，可设为 \FLEA\RedisCache::class
    'redisHost'                 => '127.0.0.1',
    'redisPort'                 => 6379,
    'redisPassword'             => '',
    'redisDb'                   => 0,
    'redisPrefix'               => 'flea:',

    /**
     * 指示要自动载入的文件
     */
    'autoLoad'                  => [],

    /**
     * 指示是否载入 session 提供程序
     */
    'sessionProvider'           => null,

    /**
     * 指示是否自动起用 session 支持
     */
    'autoSessionStart'          => true,

    /**
     * 指示使用哪些过滤器对 HTTP 请求进行过滤
     */
    'requestFilters'            => [],

    // {{{ 数据库相关

    /**
     * 数据库配置，可以是数组，也可以是 DSN 字符串
     */
    'dbDSN'                     => null,

    /**
     * 指示构造 TableDataGateway 对象时，是否自动连接到数据库
     */
    'dbTDGAutoInit'             => true,

    /**
     * 数据表的全局前缀
     */
    'dbTablePrefix'             => '',

    /**
     * 数据表元数据缓存时间（秒），如果 dbMetaCached 设置为 false，则不会缓存数据表元数据
     * 通常开发时，该设置为 10，以便修改数据库表结构后应用程序能够立刻刷新元数据
     */
    'dbMetaLifetime'            => 10,

    /**
     * 指示是否缓存数据表的元数据
     */
    'dbMetaCached'              => true,

    // {{{ View 相关

    /**
     * 要使用的模板引擎，'PHP' 表示使用 PHP 语言本身作模板引擎
     */
    'view'                      => 'PHP',

    /**
     * 模板引擎要使用的配置信息
     */
    'viewConfig'                => null,
    /**
     * 异常处理例程
     */
    'exceptionHandler'          => '__FLEA_EXCEPTION_HANDLER',

    /**
     * 指示是否显示错误信息
     */
    'jwtSecret'                 => '',     // JWT 签名密钥（必须设置）
    'jwtTtl'                    => 7200,   // JWT 有效期（秒）
    'jwtIssuer'                 => '',     // JWT 签发者
    'errorViewsDir'             => null,  // 应用层错误模板目录，null 使用框架默认    'forceJsonResponse'         => false,  // true 时所有异常均返回 JSON，适合纯 API 项目
    'displayErrors'             => true,

    /**
     * 指示是否显示友好的错误信息
     */
    'friendlyErrorsMessage'     => true,

    /**
     * 指示是否在错误信息中显示出错位置的源代码
     */
    'displaySource'             => true,

    // {{{ 助手库

    /**
     * 数据验证服务助手
     */
    'helper.verifier'           => \FLEA\Helper\Verifier::class,

    /**
     * 文件系统操作助手
     */
    'helper.file'               => \FLEA\Helper\SendFile::class,

    /**
     * 图像处理助手
     */
    'helper.image'              => \FLEA\Helper\Image::class,

    /**
     * 分页助手
     */
    'helper.pager'              => \FLEA\Helper\Pager::class,

    /**
     * 文件上传助手
     */
    'helper.uploader'           => \FLEA\Helper\FileUploader::class,

    // {{{ \FLEA\Session\Db 设置

    /**
     * 指示使用应用程序中哪一个 DSN 连接 session 数据表
     */
    'sessionDbDSN'              => 'dbDSN',

    /**
     * 指示保存 session 的数据表名称
     */
    'sessionDbTableName'        => 'sessions',

    /**
     * 指示保存 session id 的字段名
     */
    'sessionDbFieldId'          => 'sess_id',

    /**
     * 指示保存 session 数据的字段名
     */
    'sessionDbFieldData'        => 'sess_data',

    /**
     * 指示保存 session 最后活动时间的字段名
     */
    'sessionDbFieldActivity'   => 'activity',

    /**
     * 指示 session 的有效期
     * 0 表示由 PHP 运行环境决定，其他数值为超过最后一次活动时间多少秒后失效
     */
    'sessionDbLifeTime'         => 1440,

];
