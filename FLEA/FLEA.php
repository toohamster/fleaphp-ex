<?php

/**
 * 定义 FLEA 类和基础函数，并初始化 FleaPHP 运行环境
 *
 * 对于大部分 FleaPHP 的组件，都要求预先初始化 FleaPHP 环境。
 * 在应用程序中只需要通过 require('FLEA.php') 载入该文件，
 * 即可完成 FleaPHP 运行环境的初始化工作。
 *
 * @author toohamster
 * @package Core
 * @version 1.0
 */

/**
 * 保存文件载入的时间
 */
define('FLEA_LOADED_TIME', microtime());

/**
 * 定义一些有用的常量
 */

// 定义 FleaPHP 版本号常量
define('FLEA_VERSION', '1.7.1524');

// 简写的 DIRECTORY_SEPARATOR
define('DS', DIRECTORY_SEPARATOR);

// 标准 URL 模式
define('URL_STANDARD', 'URL_STANDARD');

// PATHINFO 模式
define('URL_PATHINFO', 'URL_PATHINFO');

// URL 重写模式
define('URL_REWRITE', 'URL_REWRITE');

/**#@+
 * 定义 RBAC 基本角色常量
 */
// RBAC_EVERYONE 表示任何用户（不管该用户是否具有角色信息）
define('RBAC_EVERYONE', 'RBAC_EVERYONE');

// RBAC_HAS_ROLE 表示具有任何角色的用户
define('RBAC_HAS_ROLE', 'RBAC_HAS_ROLE');

// RBAC_NO_ROLE 表示不具有任何角色的用户
define('RBAC_NO_ROLE', 'RBAC_NO_ROLE');

// RBAC_NULL 表示该设置没有值
define('RBAC_NULL', 'RBAC_NULL');

// ACTION_ALL 表示控制器中的所有动作
define('ACTION_ALL', 'ACTION_ALL');
/**#@-*/

/**
 * 初始化 FleaPHP 框架
 */

define('FLEA_DIR', __DIR__ . '/FLEA');
define('FLEA_3RD_DIR', __DIR__ . '/3rd');

// 初始化配置管理器（Composer 的 PSR-4 自动加载器会自动加载类）
use FLEA\Config;

/**
 * 载入默认设置文件
 *
 * 如果没有定义 DEPLOY_MODE 常量为 true，则使用调试模式初始化 FleaPHP
 */
if (!defined('DEPLOY_MODE') || DEPLOY_MODE != true) {
    Config::getInstance()->mergeAppInf(require(FLEA_DIR . '/Config/DEBUG_MODE_CONFIG.php'));
    define('DEBUG_MODE', true);
    if (!defined('DEPLOY_MODE')) {
        define('DEPLOY_MODE', false);
    }
} else {
    Config::getInstance()->mergeAppInf(require(FLEA_DIR . '/Config/DEPLOY_MODE_CONFIG.php'));
    define('DEBUG_MODE', false);
}

// if (DEBUG_MODE) {
//     error_reporting(error_reporting(0) & ~E_STRICT);
// } else {
//     error_reporting(0);
// }

// 注意：FLEA 框架现在使用 Composer PSR-4 自动加载器

/**
 * FLEA 类提供了 FleaPHP 框架的基本服务
 *
 * 该类的所有方法都是静态方法。
 *
 * @package Core
 * @author toohamster
 * @version 1.0
 */
class FLEA
{
    /**
     * 载入应用程序设置
     *
     * example:
     * <code>
     * FLEA::loadAppInf('./config/MyConfig.php');
     * </code>
     *
     * @param mixed $flea_internal_config 配置数组或配置文件名
     */
    public static function loadAppInf($flea_internal_config = null): void
    {
        if (!is_array($flea_internal_config) && is_string($flea_internal_config)) {
            if (!is_readable($flea_internal_config)) {
                throw new \FLEA\Exception\ExpectedFile($flea_internal_config);
            }
            $flea_internal_config = require($flea_internal_config);
        }
        if (is_array($flea_internal_config)) {
            Config::getInstance()->mergeAppInf($flea_internal_config);
        }
    }

    /**
     * 取出指定名字的设置值
     *
     * example:
     * <code>
     * FLEA::setAppInf('siteTitle');
     * .....
     * $siteTitle = FLEA::getAppInf('siteTitle');
     * </code>
     *
     * @param string $option
     * @param mixed $default
     *
     * @return mixed
     */
    public static function getAppInf(string $option, $default = null)
    {
        return Config::getInstance()->getAppInf($option, $default);
    }

    /**
     * 获得指定名字的设置值中的项目，要求该设置必须是数组
     *
     * example:
     * <code>
     * $arr = array('min' => 1, 'max' => 100, 'step' => 2);
     * FLEA::setAppInf('rule', $arr);
     * .....
     * $min = FLEA::getAppInfValue('rule', 'min');
     * </code>
     *
     * @param string $option
     * @param string $keyname
     * @param mixed $default
     *
     * @return mixed
     */
    public static function getAppInfValue(string $option, string $keyname, $default = null)
    {
        return Config::getInstance()->getAppInfValue($option, $keyname, $default);
    }

    /**
     * 设置指定名字的设置值中的项目，要求该设置值必须是数组
     *
     * @param string $option
     * @param string $keyname
     * @param mixed $value
     */
    public static function setAppInfValue(string $option, string $keyname, $value): void
    {
        Config::getInstance()->setAppInfValue($option, $keyname, $value);
    }

    /**
     * 修改设置值
     *
     * @param string $option
     * @param mixed $data
     */
    public static function setAppInf($option, $data = null): void
    {
        Config::getInstance()->setAppInf($option, $data);
    }

    /**
     * 返回指定类的唯一一个实例
     *
     * 该方法使用 Composer PSR-4 自动加载器加载类，并返回单例实例。
     * 如果类不存在，会抛出异常。
     *
     * example:
     * <code>
     * $obj = FLEA::getSingleton(\FLEA\Db\TableDataGateway::class);
     * $obj2 = FLEA::getSingleton(\FLEA\Db\TableDataGateway::class);
     * // 检查调用两次获取的是否是同一个实例
     * echo $obj === $obj2 ? 'Equals' : 'Not equals';
     * </code>
     *
     * @param string $className 完整的类名（包含命名空间）
     * @return object
     * @throws \FLEA\Exception\ExpectedClass
     */
    public static function getSingleton(string $className): object
    {
        if (FLEA::isRegistered($className)) {
            // 返回已经存在的对象实例
            return FLEA::registry($className);
        }

        // 使用 Composer PSR-4 自动加载器加载类
        if (!class_exists($className, true)) {
            throw new \FLEA\Exception\ExpectedClass($className);
        }

        return FLEA::register(new $className(), $className);
    }

    /**
     * 将一个对象实例注册到对象实例容器，以便稍后取出
     *
     * example:
     * <code>
     * $obj = new MyClass();
     * // 将对象注册到容器
     * FLEA::register($obj, 'MyClass');
     * .....
     * // 从容器查找指定的对象
     * $obj2 = FLEA::registry('MyClass');
     * // 检查是否是同一个实例
     * echo $obj === $obj2 ? 'Equals' : 'Not equals';
     * </code>
     *
     * @param object $obj
     * @param string $name
     *
     * @return object
     */
    public static function register(object $obj, ?string $name = null): ?object
    {
        return Config::getInstance()->registerObject($obj, $name);
    }

    /**
     * 从对象实例容其中取出指定名字的对象实例，如果没有指定名字则返回包含所有对象的数组
     *
     * example:参考 FLEA::register()
     *
     * @param string $name
     *
     * @return object
     */
    public static function registry(?string $name = null)
    {
        return Config::getInstance()->getRegistry($name);
    }

    /**
     * 检查指定名字的对象是否已经注册
     *
     * example:
     * <code>
     * if (FLEA::isRegistered('MyClass')) {
     *      $obj = FLEA::registry('MyClass');
     * } else {
     *      $obj = new MyClass();
     * }
     * </code>
     *
     * @param string $name
     *
     * @return boolean
     */
    public static function isRegistered(string $name): bool
    {
        return Config::getInstance()->isRegistered($name);
    }


    /**
     * 读取指定缓存的内容，如果缓存内容不存在或失效，则返回 false
     *
     * example:
     * <code>
     * $cacheId = 'my_cache_id';
     * if (!($data = FLEA::getCache($cacheId))) {
     *      $data = 'Data';
     *      FLEA::writeCache($cacheId, $data);
     * }
     * </code>
     *
     * 如果 $cacheIdIsFilename 参数为 true，则生成的缓存文件会以 $cacheId 参数作为文件名。
     * 基于安全原因，尽量不要将 $cacheIdIsFilename 参数设置为 true。
     *
     * $time 参数默认为缓存内容的有效期。其计算依据是以缓存文件的最后更新时间为准（也就是最后一次更新该缓存内容的时间）。
     *
     * 如果 $timeIsLifetime 为 false，则 $time 参数表示用于和缓存文件最更新时间进行比较的依据。
     * 如果 $time 指定的时间早于缓存文件的最后更新时间，则判断缓存内容为有效。
     *
     * @param string $cacheId 缓存ID，不同的缓存内容应该使用不同的ID
     * @param int $time 缓存过期时间或缓存生存周期
     * @param boolean $timeIsLifetime 指示 $time 参数的作用
     * @param boolean $cacheIdIsFilename 指示是否用 $cacheId 作为文件名
     *
     * @return mixed 返回缓存的内容，缓存不存在或失效则返回 false
     */
    public static function getCache(string $cacheId, int $time = 900, bool $timeIsLifetime = true, bool $cacheIdIsFilename = false)
    {
        $cacheDir = FLEA::getAppInf('internalCacheDir');
        if (is_null($cacheDir)) {
            throw new \FLEA\Exception\CacheDisabled($cacheDir);
        }

        if ($cacheIdIsFilename) {
            $cacheFile = $cacheDir . DS . preg_replace('/[^a-z0-9\-_]/i', '_', $cacheId) . '.php';
        } else {
            $cacheFile = $cacheDir . DS . md5($cacheId) . '.php';
        }
        if (!file_exists($cacheFile)) {
            return false;
        }

        if ($timeIsLifetime && $time == -1) {
            $data = safe_file_get_contents($cacheFile);
            $hash = substr($data, 16, 32);
            $data = substr($data, 48);
            if (crc32($data) != $hash || strlen($hash) != 32) {
                return false;
            }
            return $data !== false ? unserialize($data) : false;
        }

        $filetime = filemtime($cacheFile);
        if ($timeIsLifetime) {
            if (time() >= $filetime + $time) {
                return false;
            }
        } else {
            if ($time >= $filetime) {
                return false;
            }
        }
        $data = safe_file_get_contents($cacheFile);
        $hash = substr($data, 16, 32);
        $data = substr($data, 48);
        if (crc32($data) != $hash || strlen($hash) != 32) {
            return false;
        }
        return $data !== false ? unserialize($data) : false;
    }

    /**
     * 将变量内容写入缓存
     *
     * example:
     * <code>
     * $data = .....; // 要缓存的数据，可以是任何类型的值
     * // cache id 用于唯一指定一个缓存数据，以便稍后取出缓存数据
     * $cacheId = 'data_cahce_1';
     * FLEA::writeCache($cacheId, $data);
     * </code>
     *
     * @param string $cacheId
     * @param mixed $data
     * @param boolean $cacheIdIsFilename
     *
     * @return boolean
     */
    public static function writeCache(string $cacheId, $data, bool $cacheIdIsFilename = false): bool
    {
        $cacheDir = FLEA::getAppInf('internalCacheDir');
        if (is_null($cacheDir)) {
            throw new \FLEA\Exception\CacheDisabled($cacheDir);
        }

        if ($cacheIdIsFilename) {
            $cacheFile = $cacheDir . DS . preg_replace('/[^a-z0-9\-_]/i', '_', $cacheId) . '.php';
        } else {
            $cacheFile = $cacheDir . DS . md5($cacheId) . '.php';
        }

        $data = serialize($data);
        $prefix = '<?php die(); ?> ';
        $hash = sprintf('% 32d', crc32($data));
        $data = $prefix . $hash . $data;

        if (!safe_file_put_contents($cacheFile, $data)) {
            throw new \FLEA\Exception\CacheDisabled($cacheDir);
        }

        return true;
    }

    /**
     * 删除指定的缓存内容
     *
     * @param string $cacheId
     * @param boolean $cacheIdIsFilename
     *
     * @return boolean
     */
    public static function purgeCache(string $cacheId, bool $cacheIdIsFilename = false): bool
    {
        $cacheDir = FLEA::getAppInf('internalCacheDir');
        if (empty($cacheDir)) {
            throw new \FLEA\Exception\CacheDisabled($cacheDir);
        }

        if ($cacheIdIsFilename) {
            $cacheFile = $cacheDir . DS . preg_replace('/[^a-z0-9\-_]/i', '_', $cacheId) . '.php';
        } else {
            $cacheFile = $cacheDir . DS . md5($cacheId) . '.php';
        }

        if (file_exists($cacheFile)) {
            return unlink($cacheFile);
        }
        return true;
    }


    /**
     * 初始化 WebControls，返回 \FLEA\WebControls 对象实例
     *
     * 可以修改应用程序设置 webControlsClassName，指定另一个 WebControls 类。
     *
     * @return \FLEA\WebControls
     */
    public static function initWebControls(): \FLEA\WebControls
    {
        return FLEA::getSingleton(FLEA::getAppInf('webControlsClassName'));
    }

    /**
     * 初始化 Ajax，返回 \FLEA\Ajax 对象实例
     *
     * 可以修改应用程序设置 ajaxClassName，指定另一个 Ajax 类。
     *
     * @return \FLEA\Ajax
     */
    public static function initAjax(): \FLEA\Ajax
    {
        return FLEA::getSingleton(FLEA::getAppInf('ajaxClassName'));
    }

    /**
     * 载入一个助手
     *
     * 所有的助手都定义在应用程序设置中，并且以 helper. 开头。
     * 例如 helper.image 指定为 \FLEA\Helper\Image。
     *
     * @param string $helperName
     */
    public static function loadHelper(string $helperName): void
    {
        $settingName = 'helper.' . strtolower($helperName);
        $setting = FLEA::getAppInf($settingName);
        if ($setting) {
            // 使用 Composer PSR-4 自动加载
            if (!class_exists($setting, true)) {
                throw new \FLEA\Exception\ExpectedClass($setting);
            }
        } else {
            throw new \FLEA\Exception\NotExistsKeyName('helper.' . $helperName);
        }
    }

    /**
     * 返回数据库访问对象实例
     *
     * 如果不提供 $dsn 参数，或者 $dsn 参数为 0，则以应用程序设置 dbDSN 为 DSN 信息。
     *
     * DSN 是 Database Source Name 的缩写，可以理解为数据源名字。
     * 在 FleaPHP 中，DSN 是一个数组，包含了连接数据库需要的各种信息，例如主机、用户名、密码等。
     *
     * DSN 的正确写法：
     *
     * example:
     * <code>
     * $dsn = array(
     *      'driver'   => 'mysql',
     *      'host'     => 'localhost',
     *      'login'    => 'username',
     *      'password' => 'password',
     *      'database' => 'test_db',
     *      'charset'  => 'utf8',
     * );
     *
     * $dbo = FLEA::getDBO($dsn);
     * </code>
     *
     * @param array|string|int $dsn
     *
     * @return \FLEA\Db\Driver\AbstractDriver
     */
    public static function getDBO($dsn = 0): \FLEA\Db\Driver\AbstractDriver
    {
        $config = Config::getInstance();
        if ($dsn == 0) {
            $dsn = FLEA::getAppInf('dbDSN');
        }
        $dsn = FLEA::parseDSN($dsn);

        if (!is_array($dsn) || !isset($dsn['driver'])) {
            throw new \FLEA\Db\Exception\InvalidDSN($dsn);
        }

        $dsnid = $dsn['id'];
        if ($config->hasDbo($dsnid)) {
            return $config->getDbo($dsnid);
        }

        $driver = ucfirst(strtolower($dsn['driver']));
        $className = '\\FLEA\\Db\\Driver\\' . $driver;
        // 使用 Composer PSR-4 自动加载
        if (!class_exists($className, true)) {
            throw new \FLEA\Exception\ExpectedClass($className);
        }

        $dbo = new $className($dsn);
        /* @var $dbo \FLEA\Db\Driver\AbstractDriver */
        $dbo->connect();

        $config->registerDbo($dbo, $dsnid);
        return $dbo;
    }

    /**
     * 分析 DSN 字符串或数组，返回包含 DSN 连接信息的数组，失败返回 false
     *
     * @param string|array $dsn
     *
     * @return array
     */
    public static function parseDSN($dsn): ?array
    {
        if (is_array($dsn)) {
            $dsn['host'] ??= '';
            $dsn['port'] ??= '';
            $dsn['login'] ??= '';
            $dsn['password'] ??= '';
            $dsn['database'] ??= '';
            $dsn['options'] ??= '';
            $dsn['prefix'] ??= FLEA::getAppInf('dbTablePrefix');
            $dsn['schema'] ??= '';
        } else {
            $dsn = str_replace('@/', '@localhost/', $dsn);
            $parse = parse_url($dsn);
            if (empty($parse['scheme'])) {
                return null;
            }

            $dsn = [];
            $dsn['host'] = $parse['host'] ?? 'localhost';
            $dsn['port'] = $parse['port'] ?? '';
            $dsn['login'] = $parse['user'] ?? '';
            $dsn['password'] = $parse['pass'] ?? '';
            $dsn['driver'] = strtolower($parse['scheme'] ?? '');
            $dsn['database'] = isset($parse['path']) ? substr($parse['path'], 1) : '';
            $dsn['options'] = $parse['query'] ?? '';
            $dsn['prefix'] = FLEA::getAppInf('dbTablePrefix');
            $dsn['schema'] = '';
        }
        $dsnid = "{$dsn['driver']}://{$dsn['login']}:{$dsn['password']}@{$dsn['host']}_{$dsn['prefix']}/{$dsn['database']}/{$dsn['schema']}/{$dsn['options']}";
        $dsn['id'] = $dsnid;
        return $dsn;
    }

    /**
     * FleaPHP 应用程序 MVC 模式入口
     *
     * 如果应用程序需要使用 FleaPHP 提供的 MVC 模式，则在载入 FLEA.php 和自定义的应用程序设置后，应该调用 FLEA::runMVC() 启动应用程序。
     */
    public static function runMVC(): void
    {
        FLEA::init();

        // 载入调度器并转发请求到控制器
        $dispatcherClass = FLEA::getAppInf('dispatcher');

        // 使用 Composer PSR-4 自动加载
        if (!class_exists($dispatcherClass, true)) {
            throw new \FLEA\Exception\ExpectedClass($dispatcherClass);
        }

        $dispatcher = new $dispatcherClass($_GET);
        FLEA::register($dispatcher, $dispatcherClass);
        /**@var \FLEA\Dispatcher\Simple $dispatcher*/
        $dispatcher->dispatching();
    }

    /**
     * 准备运行环境
     *
     * @param boolean $loadMVC
     */
    public static function init(bool $loadMVC = false): void
    {
        static $firstTime = true;

        // 避免重复调用 FLEA::init()
        if (!$firstTime) {
            return;
        }
        $firstTime = false;

        // 设置默认时区
        if (function_exists('date_default_timezone_set')) {
            $timezone = FLEA::getAppInf('defaultTimezone');
            if (empty($timezone)) {
                $timezone = ini_get('date.timezone');
                if (empty($timezone)) {
                    // 如果服务器没有指定，则使用 Asia/ShangHai
                    date_default_timezone_set('Asia/ShangHai');
                }
            } else {
                date_default_timezone_set($timezone);
            }
        }

        /**
         * 安装应用程序指定的异常处理例程
         */
        set_exception_handler(FLEA::getAppInf('exceptionHandler'));

        /**
         * 载入日志服务提供程序
         */
        if (FLEA::getAppInf('logEnabled') && FLEA::getAppInf('logProvider')) {
            // 使用 Composer PSR-4 自动加载
            $logProviderClass = FLEA::getAppInf('logProvider');
            if (!class_exists($logProviderClass, true)) {
                throw new \FLEA\Exception\ExpectedClass($logProviderClass);
            }
        }

        /**
         * 如果没有指定缓存目录，则使用默认的缓存目录
         */
        $cachedir = FLEA::getAppInf('internalCacheDir');
        if (empty($cachedir)) {
            FLEA::setAppInf('internalCacheDir', __DIR__ . '/_Cache');
        }

        self::executeUriFilter();

        // 处理 requestFilters
        foreach ((array)FLEA::getAppInf('requestFilters') as $file) {
            // 直接 require 文件，不使用 loadFile
            if (file_exists($file)) {
                require_once($file);
            }
        }

        // 处理 autoLoad
        foreach ((array)FLEA::getAppInf('autoLoad') as $file) {
            // 直接 require 文件，不使用 loadFile
            if (file_exists($file)) {
                require_once($file);
            }
        }

        // 载入指定的 session 服务提供程序
        if (FLEA::getAppInf('sessionProvider')) {
            FLEA::getSingleton(FLEA::getAppInf('sessionProvider'));
        }
        // 自动起用 session 会话
        if (FLEA::getAppInf('autoSessionStart')) {
            session_start();
        }

        // 定义 I18N 相关的常量
        define('RESPONSE_CHARSET', FLEA::getAppInf('responseCharset'));
        define('DATABASE_CHARSET', FLEA::getAppInf('databaseCharset'));

        // 检查是否启用多语言支持
        if (FLEA::getAppInf('multiLanguageSupport')) {
            // 使用 Composer PSR-4 自动加载
            $languageProviderClass = FLEA::getAppInf('languageSupportProvider');
            if (!class_exists($languageProviderClass, true)) {
                throw new \FLEA\Exception\ExpectedClass($languageProviderClass);
            }
        }

        // 自动输出内容头信息
        if (FLEA::getAppInf('autoResponseHeader')) {
            header('Content-Type: text/html; charset=' . FLEA::getAppInf('responseCharset'));
        }
    }

    /**
     * URI 过滤器
     * 根据应用程序设置 'urlMode' 分析 $_GET 参数
     * 该函数由框架自动调用，应用程序不需要调用该函数
     *
     * @return void
     */
    private static function executeUriFilter(): void
    {
        // 根据 URL 模式设置，决定是否要执行
        if (FLEA::getAppInf('urlMode') == URL_STANDARD) {
            return;
        }

        // 调用 URI 过滤器
        $pathinfo = !empty($_SERVER['PATH_INFO']) ?
            $_SERVER['PATH_INFO'] :
            (!empty($_SERVER['ORIG_PATH_INFO']) ? $_SERVER['ORIG_PATH_INFO'] : '');

        $parts = explode('/', substr($pathinfo, 1));
        if (isset($parts[0]) && strlen($parts[0])) {
            $_GET[FLEA::getAppInf('controllerAccessor')] = $parts[0];
        }
        if (isset($parts[1]) && strlen($parts[1])) {
            $_GET[FLEA::getAppInf('actionAccessor')] = $parts[1];
        }

        $style = FLEA::getAppInf('urlParameterPairStyle');
        if ($style == '/') {
            for ($i = 2; $i < count($parts); $i += 2) {
                if (isset($parts[$i + 1])) {
                    $_GET[$parts[$i]] = $parts[$i + 1];
                }
            }
        } else {
            for ($i = 2; $i < count($parts); $i++) {
                $p = $parts[$i];
                $arr = explode($style, $p);
                if (isset($arr[1])) {
                    $_GET[$arr[0]] = $arr[1];
                }
            }
        }

        // 将 $_GET 合并到 $_REQUEST
        $_REQUEST = array_merge($_REQUEST, $_GET);
    }
}
