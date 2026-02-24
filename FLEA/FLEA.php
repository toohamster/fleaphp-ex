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
 * @version $Id: FLEA.php 1525 2008-11-25 08:34:37Z dualface $
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
define('URL_STANDARD',  'URL_STANDARD');

// PATHINFO 模式
define('URL_PATHINFO',  'URL_PATHINFO');

// URL 重写模式
define('URL_REWRITE',   'URL_REWRITE');

/**#@+
 * 定义 RBAC 基本角色常量
 */
// RBAC_EVERYONE 表示任何用户（不管该用户是否具有角色信息）
define('RBAC_EVERYONE',     'RBAC_EVERYONE');

// RBAC_HAS_ROLE 表示具有任何角色的用户
define('RBAC_HAS_ROLE',     'RBAC_HAS_ROLE');

// RBAC_NO_ROLE 表示不具有任何角色的用户
define('RBAC_NO_ROLE',      'RBAC_NO_ROLE');

// RBAC_NULL 表示该设置没有值
define('RBAC_NULL',         'RBAC_NULL');

// ACTION_ALL 表示控制器中的所有动作
define('ACTION_ALL',        'ACTION_ALL');
/**#@-*/

/**
 * 初始化 FleaPHP 框架
 */

// 初始化配置管理器（Composer 的 PSR-4 自动加载器会自动加载类）
use FLEA\Config;

$config = Config::getInstance();
$config->addClassPath(__DIR__);
define('FLEA_DIR', $config->getClassPath()[0] . DS . 'FLEA');
define('FLEA_3RD_DIR', $config->getClassPath()[0] . DS . '3rd');

/**
 * 载入默认设置文件
 *
 * 如果没有定义 DEPLOY_MODE 常量为 true，则使用调试模式初始化 FleaPHP
 */
if (!defined('DEPLOY_MODE') || DEPLOY_MODE != true) {
    $config->mergeAppInf(require(FLEA_DIR . '/Config/DEBUG_MODE_CONFIG.php'));
    define('DEBUG_MODE', true);
    if (!defined('DEPLOY_MODE')) { define('DEPLOY_MODE', false); }
} else {
    $config->mergeAppInf(require(FLEA_DIR . '/Config/DEPLOY_MODE_CONFIG.php'));
    define('DEBUG_MODE', false);
}

if (DEBUG_MODE) {
    error_reporting(error_reporting(0) & ~E_STRICT);
} else {
    error_reporting(0);
}

// 设置异常处理例程
__SET_EXCEPTION_HANDLER('__FLEA_EXCEPTION_HANDLER');

// 注册自动加载函数
spl_autoload_register(array('FLEA', 'autoload'));

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
        $config = Config::getInstance();
        if (!is_array($flea_internal_config) && is_string($flea_internal_config)) {
            if (!is_readable($flea_internal_config)) {
                throw new Exception\ExpectedFile($flea_internal_config);
            }
            $flea_internal_config = require($flea_internal_config);
        }
        if (is_array($flea_internal_config)) {
            $config->mergeAppInf($flea_internal_config);
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
        $config = Config::getInstance();
        return $config->getAppInf($option, $default);
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
     * 导入文件搜索路径
     *
     * FLEA::loadClass()、FLEA::getSingleton() 会在搜索路径中查找指定名字的类定义文件。
     * 因此需要调用 FLEA::import() 将存放类定义文件的目录添加到搜索路径中。
     * 但是，不应该将类文件所在目录直接添加到搜索路径中，而是根据类的命名来决定要添加哪一个目录。
     *
     * 例如类名称是 Table_Posts，而实际的文件是 ./APP/Table/Posts.php。
     * 那么应该添加的目录就是 ./APP，而不是 ./APP/Table 。
     *
     * example:
     * <code>
     * FLEA::import(APP_DIR . '/LIBS');
     * </code>
     *
     * @param string $dir
     */
    public static function import(string $dir): void
    {
        Config::getInstance()->addClassPath($dir);
    }

    /**
     * 自动加载类文件
     *
     * @param string $className 要加载的类名
     * @return boolean 加载成功返回true，失败返回false
     */
    public static function autoload(string $className): bool
    {
        // 检查类是否已经加载
        if (class_exists($className, false) || interface_exists($className, false)) {
            return true;
        }

        // 使用内部的 loadClass 方法来加载类
        return self::loadClass($className, true);
    }

    /**
     * 载入指定的文件
     *
     * FLEA::loadFile() 会 $filename 参数中的 “_” 替换为目录，例如：
     *
     * example:
     * <code>
     * FLEA::loadFile('Table_Posts.php');
     * // 等同于 include 'Table/Posts.php';
     * </code>
     *
     * @param string $className
     * @param boolean $loadOnce 指定为 true 时，FLEA::loadFile() 等同于 require_once
     *
     * @return boolean
     */
    public static function loadFile(string $filename, bool $loadOnce = false): bool
    {
        static $is_loaded = [];

        $path = FLEA::getFilePath($filename);
        if ($path != '') {
            if (isset($is_loaded[$path]) && $loadOnce) { return true; }
            $is_loaded[$path] = true;
            if ($loadOnce) {
                return require_once($path);
            } else {
                return require($path);
            }
        }

        throw new Exception\ExpectedFile($filename);
    }

    /**
     * 载入指定类的定义文件
     *
     * 类名称中的 “_” 会被替换为目录，然后从搜索路径中查找该类的定义文件。
     *
     * example:
     * <code>
     * // 首先将类名称 Table_Posts 转换为文件名 Table/Posts.php
     * // 然后从搜索路径中查找 Table/Posts.php 文件
     * </code>
     *
     * @param string $filename
     * @param boolean $noException 如果为 true，则类定义文件没找到时不抛出异常
     *
     * @return boolean
     */
    public static function loadClass(string $className, bool $noException = false): bool
    {
        if (class_exists($className, false) || interface_exists($className, false)) { return true; }

        if (preg_match('/[^a-z0-9\-_.]/i', $className) === 0) {
            $filename = FLEA::getFilePath($className . '.php');
            if ($filename) {
                require($filename);
                if (class_exists($className, false) || interface_exists($className, false)) { return true; }
            }
        }

        if ($noException) { return false; }

        $filename = FLEA::getFilePath($className . '.php', true);
        throw new Exception\ExpectedClass($className, $filename, file_exists($filename));
    }

    /**
     * 按照 FleaPHP 中命名规则，搜索文件
     *
     * FleaPHP 的命名规则就是文件名中的“_”替换为目录分隔符。
     *
     * @param string $filename
     * @param boolean $return 指示是否直接返回处理后的文件名，而不判断文件是否存在
     *
     * @return string
     */
    public static function getFilePath(string $filename, bool $return = false): ?string
    {
        $filename = str_replace('_', DIRECTORY_SEPARATOR, $filename);
        if (DIRECTORY_SEPARATOR == '/') {
            $filename = str_replace('\\', DIRECTORY_SEPARATOR, $filename);
        } else {
            $filename = str_replace('/', DIRECTORY_SEPARATOR, $filename);
        }

        if (strtolower(substr($filename, -4)) != '.php') {
            $filename .= '.php';
        }

        // 首先搜索当前目录
        if (is_file($filename)) { return $filename; }

        $config = Config::getInstance();
        foreach ($config->getClassPath() as $classdir) {
            $path = $classdir . DIRECTORY_SEPARATOR . $filename;
            if (is_file($path)) { return $path; }
        }

        if ($return) { return $filename; }
        return false;
    }

    /**
     * 返回指定类的唯一一个实例
     *
     * example:
     * <code>
     * $obj = FLEA::getSingleton('Table_Posts);
     * ......
     * $obj2 = FLEA::getSingleton('Table_Posts);
     * // 检查调用两次获取的是否是同一个实例
     * echo $obj === $obj2 ? 'Equals' : 'Not equals';
     * </code>
     *
     * @param string $className
     *
     * @return object
     */
    public static function getSingleton(string $className): object
    {
        static $instances = [];
        if (FLEA::isRegistered($className)) {
            // 返回已经存在的对象实例
            return FLEA::registry($className);
        }
        $classExists = class_exists($className, false);
        if (!$classExists) {
            if (!FLEA::loadClass($className)) {
                $return = false;
                return $return;
            }
        }

        $instances[$className] = new $className();
        FLEA::register($instances[$className], $className);
        return $instances[$className];
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
        $config = Config::getInstance();
        return $config->registerObject($obj, $name);
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
        $config = Config::getInstance();
        return $config->getRegistry($name);
    }

    /**
     * 检查指定名字的对象是否已经注册
     *
     * example:
     * <code>
     * if (FLEA::isRegistered('MyClass')) {
     *      $obj =& FLEA::registry('MyClass');
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
        $config = Config::getInstance();
        return $config->isRegistered($name);
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
            throw new Exception\CacheDisabled($cacheDir);
        }

        if ($cacheIdIsFilename) {
            $cacheFile = $cacheDir . DS . preg_replace('/[^a-z0-9\-_]/i', '_', $cacheId) . '.php';
        } else {
            $cacheFile = $cacheDir . DS . md5($cacheId) . '.php';
        }
        if (!file_exists($cacheFile)) { return false; }

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
            if (time() >= $filetime + $time) { return false; }
        } else {
            if ($time >= $filetime) { return false; }
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
            throw new Exception\CacheDisabled($cacheDir);
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
            throw new Exception\CacheDisabled($cacheDir);
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
        if (is_null($cacheDir)) {
            throw new Exception\CacheDisabled($cacheDir);
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
     * 初始化 WebControls，返回 FLEA_WebControls 对象实例
     *
     * 可以修改应用程序设置 webControlsClassName，指定另一个 WebControls 类。
     *
     * @return FLEA_WebControls
     */
    public static function initWebControls(): FLEA_WebControls
    {
        return FLEA::getSingleton(FLEA::getAppInf('webControlsClassName'));
    }

    /**
     * 初始化 Ajax，返回 FLEA_Ajax 对象实例
     *
     * 可以修改应用程序设置 ajaxClassName，指定另一个 Ajax 类。
     *
     * @return FLEA_Ajax
     */
    public static function initAjax(): FLEA_Ajax
    {
        return FLEA::getSingleton(FLEA::getAppInf('ajaxClassName'));
    }

    /**
     * 载入一个助手
     *
     * 所有的助手都定义在应用程序设置中，并且以 helper. 开头。
     * 例如 helper.array 指定为 \FLEA\Helper\Array、helper.image 指定为 \FLEA\Helper\Image。
     *
     * @param string $helperName
     */
    public static function loadHelper(string $helperName): void
    {
        $settingName = 'helper.' . strtolower($helperName);
        $setting = FLEA::getAppInf($settingName);
        if ($setting) {
            FLEA::loadFile($setting, true);
        } else {
            throw new Exception\NotExistsKeyName('helper.' . $helperName);
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
            throw new FLEA_Db_Exception_InvalidDSN($dsn);
        }

        $dsnid = $dsn['id'];
        if ($config->hasDbo($dsnid)) {
            return $config->getDbo($dsnid);
        }

        $driver = ucfirst(strtolower($dsn['driver']));
        $className = 'FLEA_Db_Driver_' . $driver;
        if ($driver == 'Mysql' || $driver == 'Mysqlt') {
            require_once(FLEA_DIR . '/Db/Driver/Mysql.php');
        } else {
            FLEA::loadClass($className);
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
            $dsn['host'] = isset($dsn['host']) ? $dsn['host'] : '';
            $dsn['port'] = isset($dsn['port']) ? $dsn['port'] : '';
            $dsn['login'] = isset($dsn['login']) ? $dsn['login'] : '';
            $dsn['password'] = isset($dsn['password']) ? $dsn['password'] : '';
            $dsn['database'] = isset($dsn['database']) ? $dsn['database'] : '';
            $dsn['options'] = isset($dsn['options']) ? $dsn['options'] : '';
            $dsn['prefix'] = isset($dsn['prefix']) ? $dsn['prefix'] : FLEA::getAppInf('dbTablePrefix');
            $dsn['schema'] = isset($dsn['schema']) ? $dsn['schema'] : '';
        } else {
            $dsn = str_replace('@/', '@localhost/', $dsn);
            $parse = parse_url($dsn);
            if (empty($parse['scheme'])) { return false; }

            $dsn = [];
            $dsn['host']     = isset($parse['host']) ? $parse['host'] : 'localhost';
            $dsn['port']     = isset($parse['port']) ? $parse['port'] : '';
            $dsn['login']    = isset($parse['user']) ? $parse['user'] : '';
            $dsn['password'] = isset($parse['pass']) ? $parse['pass'] : '';
            $dsn['driver']   = isset($parse['scheme']) ? strtolower($parse['scheme']) : '';
            $dsn['database'] = isset($parse['path']) ? substr($parse['path'], 1) : '';
            $dsn['options']  = isset($parse['query']) ? $parse['query'] : '';
            $dsn['prefix'] = FLEA::getAppInf('dbTablePrefix');
            $dsn['schema']   = '';
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
        $MVCPackageFilename = FLEA::getAppInf('MVCPackageFilename');
        if ($MVCPackageFilename != '') {
            require_once($MVCPackageFilename);
        }
        FLEA::init();

        // 载入调度器并转发请求到控制器
        $dispatcherClass = FLEA::getAppInf('dispatcher');
        FLEA::loadClass($dispatcherClass);

        $dispatcher = new $dispatcherClass($_GET);
        FLEA::register($dispatcher, $dispatcherClass);
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
        if (!$firstTime) { return; }
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
        __SET_EXCEPTION_HANDLER(FLEA::getAppInf('exceptionHandler'));
        set_exception_handler(FLEA::getAppInf('exceptionHandler'));

        /**
         * 载入日志服务提供程序
         */
        if (FLEA::getAppInf('logEnabled') && FLEA::getAppInf('logProvider')) {
            FLEA::loadClass(FLEA::getAppInf('logProvider'));
        }

        /**
         * 如果没有指定缓存目录，则使用默认的缓存目录
         */
        $cachedir = FLEA::getAppInf('internalCacheDir');
        if (empty($cachedir)) {
            FLEA::setAppInf('internalCacheDir', __DIR__ . DS . '_Cache');
        }

        // 根据 URL 模式设置，决定是否要载入 URL 分析过滤器
        if (FLEA::getAppInf('urlMode') != URL_STANDARD) {
            // 调用 URI 过滤器
            if (defined('FLEA_VERSION')) {
                ___uri_filter();
            }
        }

        // 处理 requestFilters
        foreach ((array)FLEA::getAppInf('requestFilters') as $file) {
            FLEA::loadFile($file);
        }

        // 处理 $loadMVC
        if ($loadMVC) {
            $MVCPackageFilename = FLEA::getAppInf('MVCPackageFilename');
            if ($MVCPackageFilename != '') {
                require_once($MVCPackageFilename);
            }
        }

        // 处理 autoLoad
        foreach ((array)FLEA::getAppInf('autoLoad') as $file) {
            FLEA::loadFile($file);
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
            FLEA::loadClass(FLEA::getAppInf('languageSupportProvider'));
        }
        if (!function_exists('_T')) {
            function _T() {};
        }

        // 自动输出内容头信息
        if (FLEA::getAppInf('autoResponseHeader')) {
            header('Content-Type: text/html; charset=' . FLEA::getAppInf('responseCharset'));
        }
    }
}

