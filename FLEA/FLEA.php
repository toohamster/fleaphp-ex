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
global $___fleaphp_loaded_time;
$___fleaphp_loaded_time = microtime();

/**
 * 定义一些有用的常量
 */

// 定义 FleaPHP 版本号常量
define('FLEA_VERSION', '1.7.1524');

// 定义指示 PHP4 或 PHP5 的常量
define('PHP5', true);
define('PHP4', false);

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
define('G_FLEA_VAR', '__FLEA_CORE__');

// 初始化配置管理器
$config = FLEA_Config::getInstance();
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
     * @param mixed $__config 配置数组或配置文件名
     */
    public static function loadAppInf($__flea_internal_config = null)
    {
        if (!is_array($__flea_internal_config) && is_string($__flea_internal_config)) {
            if (!is_readable($__flea_internal_config)) {
                throw new FLEA_Exception_ExpectedFile($__flea_internal_config);
            }
            $__flea_internal_config = require($__flea_internal_config);
        }
        if (is_array($__flea_internal_config)) {
            $GLOBALS[G_FLEA_VAR]['APP_INF'] = array_merge($GLOBALS[G_FLEA_VAR]['APP_INF'], $__flea_internal_config);
        }
        return null;
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
    public static function getAppInf($option, $default = null)
    {
        return isset($GLOBALS[G_FLEA_VAR]['APP_INF'][$option]) ? $GLOBALS[G_FLEA_VAR]['APP_INF'][$option] : $default;
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
    public static function getAppInfValue($option, $keyname, $default = null)
    {
        if (!isset($GLOBALS[G_FLEA_VAR]['APP_INF'][$option])) {
            $GLOBALS[G_FLEA_VAR]['APP_INF'][$option] = [];
        }
        if (array_key_exists($keyname, $GLOBALS[G_FLEA_VAR]['APP_INF'][$option])) {
            return $GLOBALS[G_FLEA_VAR]['APP_INF'][$option][$keyname];
        } else {
            $GLOBALS[G_FLEA_VAR]['APP_INF'][$option][$keyname] = $default;
            return $default;
        }
    }

    /**
     * 设置指定名字的设置值中的项目，要求该设置值必须是数组
     *
     * @param string $option
     * @param string $keyname
     * @param mixed $value
     */
    public static function setAppInfValue($option, $keyname, $value)
    {
        if (!isset($GLOBALS[G_FLEA_VAR]['APP_INF'][$option])) {
            $GLOBALS[G_FLEA_VAR]['APP_INF'][$option] = [];
        }
        $GLOBALS[G_FLEA_VAR]['APP_INF'][$option][$keyname] = $value;
    }

    /**
     * 修改设置值
     *
     * @param string $option
     * @param mixed $data
     */
    public static function setAppInf($option, $data = null)
    {
        if (is_array($option)) {
            $GLOBALS[G_FLEA_VAR]['APP_INF'] = array_merge($GLOBALS[G_FLEA_VAR]['APP_INF'], $option);
        } else {
            $GLOBALS[G_FLEA_VAR]['APP_INF'][$option] = $data;
        }
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
    public static function import($dir)
    {
        if (array_search($dir, $GLOBALS[G_FLEA_VAR]['CLASS_PATH'], true)) { return; }
        if (DIRECTORY_SEPARATOR == '/') {
            $dir = str_replace('\\', DIRECTORY_SEPARATOR, $dir);
        } else {
            $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
        }
        $GLOBALS[G_FLEA_VAR]['CLASS_PATH'][] = $dir;
    }

    /**
     * 自动加载类文件
     *
     * @param string $className 要加载的类名
     * @return boolean 加载成功返回true，失败返回false
     */
    public static function autoload($className)
    {
        // 检查类是否已经加载
        if (PHP5) {
            if (class_exists($className, false) || interface_exists($className, false)) { 
                return true; 
            }
        } else {
            if (class_exists($className)) { 
                return true; 
            }
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
    public static function loadFile($filename, $loadOnce = false)
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

        throw new FLEA_Exception_ExpectedFile($filename);
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
    public static function loadClass($className, $noException = false)
    {
        if (PHP5) {
            if (class_exists($className, false) || interface_exists($className, false)) { return true; }
        } else {
            if (class_exists($className)) { return true; }
        }

        if (preg_match('/[^a-z0-9\-_.]/i', $className) === 0) {
            $filename = FLEA::getFilePath($className . '.php');
            if ($filename) {
                require($filename);
                if (PHP5) {
                    if (class_exists($className, false) || interface_exists($className, false)) { return true; }
                } else {
                    if (class_exists($className)) { return true; }
                }
            }
        }

        if ($noException) { return false; }

        $filename = FLEA::getFilePath($className . '.php', true);
        require_once(FLEA_DIR . '/Exception/ExpectedClass.php');
        __THROW(new FLEA_Exception_ExpectedClass($className, $filename, file_exists($filename)));
        return false;
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
    public static function getFilePath($filename, $return = false)
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

        foreach ($GLOBALS[G_FLEA_VAR]['CLASS_PATH'] as $classdir) {
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
    public static function getSingleton($className)
    {
        static $instances = [];
        if (FLEA::isRegistered($className)) {
            // 返回已经存在的对象实例
            return FLEA::registry($className);
        }
        if (PHP5) {
            $classExists = class_exists($className, false);
        } else {
            $classExists = class_exists($className);
        }
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
    public static function register(& $obj, $name = null)
    {
        if (!is_object($obj)) {
            return __THROW(new FLEA_Exception_TypeMismatch($obj, 'object', gettype($obj)));
        }

        if (is_null($name)) {
            $name = get_class($obj);
        }

        if (isset($GLOBALS[G_FLEA_VAR]['OBJECTS'][$name])) {
            return __THROW(new FLEA_Exception_ExistsKeyName($name));
        } else {
            $GLOBALS[G_FLEA_VAR]['OBJECTS'][$name] =& $obj;
            return $obj;
        }
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
    public static function registry($name = null)
    {
        if (is_null($name)) {
            return $GLOBALS[G_FLEA_VAR]['OBJECTS'];
        }
        if (isset($GLOBALS[G_FLEA_VAR]['OBJECTS'][$name]) && is_object($GLOBALS[G_FLEA_VAR]['OBJECTS'][$name])) {
            return $GLOBALS[G_FLEA_VAR]['OBJECTS'][$name];
        }
        return __THROW(new FLEA_Exception_NotExistsKeyName($name));
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
    public static function isRegistered($name)
    {
        return isset($GLOBALS[G_FLEA_VAR]['OBJECTS'][$name]);
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
    public static function getCache($cacheId, $time = 900, $timeIsLifetime = true, $cacheIdIsFilename = false)
    {
        $cacheDir = FLEA::getAppInf('internalCacheDir');
        if (is_null($cacheDir)) {
            throw new FLEA_Exception_CacheDisabled($cacheDir);
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
    public static function writeCache($cacheId, $data, $cacheIdIsFilename = false)
    {
        $cacheDir = FLEA::getAppInf('internalCacheDir');
        if (is_null($cacheDir)) {
            throw new FLEA_Exception_CacheDisabled($cacheDir);
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
            throw new FLEA_Exception_CacheDisabled($cacheDir);
        } else {
            return true;
        }
    }

    /**
     * 删除指定的缓存内容
     *
     * @param string $cacheId
     * @param boolean $cacheIdIsFilename
     *
     * @return boolean
     */
    public static function purgeCache($cacheId, $cacheIdIsFilename = false)
    {
        $cacheDir = FLEA::getAppInf('internalCacheDir');
        if (is_null($cacheDir)) {
            throw new FLEA_Exception_CacheDisabled($cacheDir);
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
    public static function initWebControls()
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
    public static function initAjax()
    {
        return FLEA::getSingleton(FLEA::getAppInf('ajaxClassName'));
    }

    /**
     * 载入一个助手
     *
     * 所有的助手都定义在应用程序设置中，并且以 helper. 开头。
     * 例如 helper.array 指定为 FLEA_Helper_Array、helper.image 指定为 FLEA_Helper_Image。
     *
     * @param string $helperName
     */
    public static function loadHelper($helperName)
    {
        $settingName = 'helper.' . strtolower($helperName);
        $setting = FLEA::getAppInf($settingName);
        if ($setting) {
            return FLEA::loadFile($setting, true);
        } else {
            throw new FLEA_Exception_NotExistsKeyName('helper.' . $helperName);
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
     * $dbo =& FLEA::getDBO($dsn);
     * </code>
     *
     * @param array|string|int $dsn
     *
     * @return FLEA_Db_Driver_Abstract
     */
    public static function getDBO($dsn = 0)
    {
        if ($dsn == 0) {
            $dsn = FLEA::getAppInf('dbDSN');
        }
        $dsn = FLEA::parseDSN($dsn);

        if (!is_array($dsn) || !isset($dsn['driver'])) {
            throw new FLEA_Db_Exception_InvalidDSN($dsn);
        }

        $dsnid = $dsn['id'];
        if (isset($GLOBALS[G_FLEA_VAR]['DBO'][$dsnid])) {
            return $GLOBALS[G_FLEA_VAR]['DBO'][$dsnid];
        }

        $driver = ucfirst(strtolower($dsn['driver']));
        $className = 'FLEA_Db_Driver_' . $driver;
        if ($driver == 'Mysql' || $driver == 'Mysqlt') {
            require_once(FLEA_DIR . '/Db/Driver/Mysql.php');
        } else {
            FLEA::loadClass($className);
        }
        $dbo = new $className($dsn);
        /* @var $dbo FLEA_Db_Driver_Abstract */
        $dbo->connect();

        $GLOBALS[G_FLEA_VAR]['DBO'][$dsnid] =& $dbo;
        return $GLOBALS[G_FLEA_VAR]['DBO'][$dsnid];
    }

    /**
     * 分析 DSN 字符串或数组，返回包含 DSN 连接信息的数组，失败返回 false
     *
     * @param string|array $dsn
     *
     * @return array
     */
    public static function parseDSN($dsn)
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
    public static function runMVC()
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
    public static function init($loadMVC = false)
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
        if (PHP5) {
            set_exception_handler(FLEA::getAppInf('exceptionHandler'));
        }

        /**
         * 载入日志服务提供程序
         */
        if (FLEA::getAppInf('logEnabled') && FLEA::getAppInf('logProvider')) {
            FLEA::loadClass(FLEA::getAppInf('logProvider'));
        }
        if (!function_exists('log_message')) {
            // 如果没有指定日志服务提供程序，就定义一个空的 log_message() 函数
            function log_message() {};
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
            require(FLEA_DIR . '/Filter/Uri.php');
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

/**
 * 定义 FleaPHP 基本的全局函数
 */

/**
 * 重定向浏览器到指定的 URL
 *
 * @param string $url 要重定向的 url
 * @param int $delay 等待多少秒以后跳转
 * @param bool $js 指示是否返回用于跳转的 JavaScript 代码
 * @param bool $jsWrapped 指示返回 JavaScript 代码时是否使用 <script> 标签进行包装
 * @param bool $return 指示是否返回生成的 JavaScript 代码
 */
function redirect($url, $delay = 0, $js = false, $jsWrapped = true, $return = false)
{
    $delay = (int)$delay;
    if (!$js) {
        if (headers_sent() || $delay > 0) {
            echo <<<EOT
    <html>
    <head>
    <meta http-equiv="refresh" content="{$delay};URL={$url}" />
    </head>
    </html>
EOT;
            exit;
        } else {
            header("Location: {$url}");
            exit;
        }
    }

    $out = '';
    if ($jsWrapped) {
        $out .= '<script language="JavaScript" type="text/javascript">';
    }
    if ($delay > 0) {
        $out .= "window.setTimeout(function () { document.location='{$url}'; }, {$delay});";
    } else {
        $out .= "document.location='{$url}';";
    }
    if ($jsWrapped) {
        $out .= '</script>';
    }

    if ($return) {
        return $out;
    }

    echo $out;
    exit;
}

/**
 * 构造 url
 *
 * 构造 url 需要提供两个参数：控制器名称和控制器动作名。如果省略这两个参数或者其中一个。
 * 则 url() 函数会使用应用程序设置中的确定的默认控制名称和默认控制器动作名。
 *
 * url() 会根据应用程序设置 urlMode 生成不同的 URL 地址：
 * - URL_STANDARD - 标准模式（默认），例如 index.php?url=Login&action=Reject&id=1
 * - URL_PATHINFO - PATHINFO 模式，例如 index.php/Login/Reject/id/1
 * - URL_REWRITE  - URL 重写模式，例如 /Login/Reject/id/1
 *
 * 生成的 url 地址，还要受下列应用程序设置的影响：
 *   - controllerAccessor
 *   - defaultController
 *   - actionAccessor
 *   - defaultAction
 *   - urlMode
 *   - urlLowerChar
 *
 * 用法：
 * <code>
 * $url = url('Login', 'checkUser');
 * // $url 现在为 ?controller=Login&action=checkUser
 *
 * $url = url('Login', 'checkUser', array('username' => 'dualface'));
 * // $url 现在为 ?controller=Login&action=checkUser&username=dualface
 *
 * $url = url('Article', 'View', array('id' => 1'), '#details');
 * // $url 现在为 ?controller=Article&action=View&id=1#details
 * </code>
 *
 * @param string $controllerName
 * @param string $actionName
 * @param array $params
 * @param string $anchor
 * @param array $options
 *
 * @return string
 */
function url($controllerName = null, $actionName = null, $params = null, $anchor = null, $options = null)
{
    static $baseurl = null, $currentBootstrap = null;

    $callback = FLEA::getAppInf('urlCallback');
    if (!empty($callback)) {
        call_user_func_array($callback, array(& $controllerName, & $actionName, & $params, & $anchor, & $options));
    }

    // 确定当前的 URL 基础地址和入口文件名
    if (is_null($baseurl)) {
        $baseurl = detect_uri_base();
        $p = strrpos($baseurl, '/');
        $currentBootstrap = substr($baseurl, $p + 1);
        $baseurl = substr($baseurl, 0, $p);
    }

    // 确定生成 url 要使用的 bootstrap
    $options = (array)$options;
    if (isset($options['bootstrap'])) {
        $bootstrap = $options['bootstrap'];
    } else if ($currentBootstrap == '') {
        $bootstrap = FLEA::getAppInf('urlBootstrap');
    } else {
        $bootstrap = $currentBootstrap;
    }

    // 确定控制器和动作的名字
    $defaultController = FLEA::getAppInf('defaultController');
    $defaultAction = FLEA::getAppInf('defaultAction');
    $lowerChar = isset($options['lowerChar']) ? $options['lowerChar'] : FLEA::getAppInf('urlLowerChar');
    if ($lowerChar) {
        $defaultController = strtolower($defaultController);
        $defaultAction = strtolower($defaultAction);
    }

    if ($bootstrap != $currentBootstrap && $currentBootstrap != '') {
        $controllerName = !empty($controllerName) ? $controllerName : null;
        $actionName = !empty($actionName) ? $actionName : null;
    } else {
        $controllerName = !empty($controllerName) ? $controllerName : $defaultController;
        $actionName = !empty($actionName) ? $actionName : $defaultAction;
    }
    if ($lowerChar) {
        $controllerName = strtolower($controllerName);
        $actionName = strtolower($actionName);
    }

    $url = '';
    $mode = isset($options['mode']) ? $options['mode'] : FLEA::getAppInf('urlMode');

    // PATHINFO 和 REWRITE 模式
    if ($mode == URL_PATHINFO || $mode == URL_REWRITE) {
        $url = $baseurl;
        if ($mode == URL_PATHINFO) {
            $url .= '/' . $bootstrap;
        }
        if ($controllerName != '' && $actionName != '') {
            $pps = isset($options['parameterPairStyle']) ? $options['parameterPairStyle'] : FLEA::getAppInf('urlParameterPairStyle');
            $url .= '/' . rawurlencode($controllerName);
            if (is_array($params) && !empty($params)) {
                $url .= '/' . rawurlencode($actionName);
                $url .= '/' . encode_url_args($params, $mode, $pps);
            } else {
                if (FLEA::getAppInf('urlAlwaysUseAccessor') || $actionName != $defaultAction) {
                    $url .= '/' . rawurlencode($actionName);
                }
            }
        }
        if ($anchor) { $url .= '#' . $anchor; }
        return $url;
    }

    // 标准模式
    $alwaysUseBootstrap = isset($options['alwaysUseBootstrap']) ? $options['alwaysUseBootstrap'] : FLEA::getAppInf('urlAlwaysUseBootstrap');
    $url = $baseurl . '/';

    if ($alwaysUseBootstrap || $bootstrap != FLEA::getAppInf('urlBootstrap')) {
        $url .= $bootstrap;
    }

    $parajoin = '?';
    if (FLEA::getAppInf('urlAlwaysUseAccessor')) {
        $defaultController = '';
        $defaultAction = '';
    }
    if ($controllerName != $defaultController && !is_null($controllerName)) {
        $url .= $parajoin . FLEA::getAppInf('controllerAccessor'). '=' . $controllerName;
        $parajoin = '&';
    }
    if ($actionName != $defaultAction && !is_null($actionName)) {
        $url .= $parajoin . FLEA::getAppInf('actionAccessor') . '=' . $actionName;
        $parajoin = '&';
    }

    if (is_array($params) && !empty($params)) {
        $url .= $parajoin . encode_url_args($params, $mode);
    }
    if ($anchor) { $url .= '#' . $anchor; }

    return $url;
}

/**
 * 获得当前请求的 URL 地址
 *
 * 参考 QeePHP 和 Zend Framework 实现。
 *
 * @return string
 */
function detect_uri_base()
{
    static $baseuri = null;

    if ($baseuri) { return $baseuri; }
    $filename = basename($_SERVER['SCRIPT_FILENAME']);

    if (basename($_SERVER['SCRIPT_NAME']) === $filename) {
        $url = $_SERVER['SCRIPT_NAME'];
    } elseif (basename($_SERVER['PHP_SELF']) === $filename) {
        $url = $_SERVER['PHP_SELF'];
    } elseif (isset($_SERVER['ORIG_SCRIPT_NAME']) && basename($_SERVER['ORIG_SCRIPT_NAME']) === $filename) {
        $url = $_SERVER['ORIG_SCRIPT_NAME']; // 1and1 shared hosting compatibility
    } else {
        // Backtrack up the script_filename to find the portion matching
        // php_self
        $path    = $_SERVER['PHP_SELF'];
        $segs    = explode('/', trim($_SERVER['SCRIPT_FILENAME'], '/'));
        $segs    = array_reverse($segs);
        $index   = 0;
        $last    = count($segs);
        $url = '';
        do {
            $seg     = $segs[$index];
            $url = '/' . $seg . $url;
            ++$index;
        } while (($last > $index) && (false !== ($pos = strpos($path, $url))) && (0 != $pos));
    }

    // Does the baseUrl have anything in common with the request_uri?
    if (isset($_SERVER['HTTP_X_REWRITE_URL'])) { // check this first so IIS will catch
        $request_uri = $_SERVER['HTTP_X_REWRITE_URL'];
    } elseif (isset($_SERVER['REQUEST_URI'])) {
        $request_uri = $_SERVER['REQUEST_URI'];
    } elseif (isset($_SERVER['ORIG_PATH_INFO'])) { // IIS 5.0, PHP as CGI
        $request_uri = $_SERVER['ORIG_PATH_INFO'];
        if (!empty($_SERVER['QUERY_STRING'])) {
            $request_uri .= '?' . $_SERVER['QUERY_STRING'];
        }
    } else {
        $request_uri = '';
    }

    if (0 === strpos($request_uri, $url)) {
        // full $url matches
        $baseuri = $url;
        return $baseuri;
    }

    if (0 === strpos($request_uri, dirname($url))) {
        // directory portion of $url matches
        $baseuri = rtrim(dirname($url), '/') . '/';
        return $baseuri;
    }

    if (!strpos($request_uri, basename($url))) {
        // no match whatsoever; set it blank
        return '';
    }

    // If using mod_rewrite or ISAPI_Rewrite strip the script filename
    // out of baseUrl. $pos !== 0 makes sure it is not matching a value
    // from PATH_INFO or QUERY_STRING
    if ((strlen($request_uri) >= strlen($url))
        && ((false !== ($pos = strpos($request_uri, $url))) && ($pos !== 0)))
    {
        $url = substr($request_uri, 0, $pos + strlen($url));
    }

    $baseuri = rtrim($url, '/') . '/';
    return $baseuri;
}

/**
 * 将数组转换为可通过 url 传递的字符串连接
 *
 * 用法：
 * <code>
 * $string = encode_url_args(array('username' => 'dualface', 'mode' => 'md5'));
 * // $string 现在为 username=dualface&mode=md5
 * </code>
 *
 * @param array $args
 * @param enum $urlMode
 * @param string $parameterPairStyle
 *
 * @return string
 */
function encode_url_args($args, $urlMode = URL_STANDARD, $parameterPairStyle = null)
{
    $str = '';
    switch ($urlMode) {
    case URL_STANDARD:
        if (is_null($parameterPairStyle)) {
            $parameterPairStyle = '=';
        }
        $sc = '&';
        break;
    case URL_PATHINFO:
    case URL_REWRITE:
        if (is_null($parameterPairStyle)) {
            $parameterPairStyle = FLEA::getAppInf('urlParameterPairStyle');
        }
        $sc = '/';
        break;
    }

    foreach ($args as $key => $value) {
        if (is_null($value) || $value === '') { continue; }
        if (is_array($value)) {
            $append = encode_url_args($value, $urlMode);
        } else {
            $append = rawurlencode($key) . $parameterPairStyle . rawurlencode($value);
        }
        if (substr($str, -1) != $sc) {
            $str .= $sc;
        }
        $str .= $append;
    }
    return substr($str, 1);
}

/**
 * 转换 HTML 特殊字符，等同于 htmlspecialchars()
 *
 * @param string $text
 *
 * @return string
 */
function h($text)
{
    return htmlspecialchars($text);
}

/**
 * 转换 HTML 特殊字符以及空格和换行符
 *
 * 空格替换为 &nbsp; ，换行符替换为 <br />。
 *
 * @param string $text
 *
 * @return string
 */
function t($text)
{
    return nl2br(str_replace(' ', '&nbsp;', htmlspecialchars($text)));
}

/**
 * 通过 JavaScript 脚本显示提示对话框，并关闭窗口或者重定向浏览器
 *
 * 用法：
 * <code>
 * js_alert('Dialog message', '', $url);
 * // 或者
 * js_alert('Dialog message', 'window.close();');
 * </code>
 *
 * @param string $message 要显示的消息
 * @param string $after_action 显示消息后要执行的动作
 * @param string $url 重定向位置
 */
function js_alert($message = '', $after_action = '', $url = '')
{
    $out = "<script language=\"javascript\" type=\"text/javascript\">\n";
    if (!empty($message)) {
        $out .= "alert(\"";
        $out .= str_replace("\\\\n", "\\n", t2js(addslashes($message)));
        $out .= "\");\n";
    }
    if (!empty($after_action)) {
        $out .= $after_action . "\n";
    }
    if (!empty($url)) {
        $out .= "document.location.href=\"";
        $out .= $url;
        $out .= "\";\n";
    }
    $out .= "</script>";
    echo $out;
    exit;
}

/**
 * 将任意字符串转换为 JavaScript 字符串（不包括首尾的"）
 *
 * @param string $content
 *
 * @return string
 */
function t2js($content)
{
    return str_replace(array("\r", "\n"), array('', '\n'), addslashes($content));
}

/**
 * safe_file_put_contents() 一次性完成打开文件，写入内容，关闭文件三项工作，并且确保写入时不会造成并发冲突
 *
 * @param string $filename
 * @param string $content
 * @param int $flag
 *
 * @return boolean
 */
function safe_file_put_contents($filename, & $content)
{
    $fp = fopen($filename, 'wb');
    if ($fp) {
        flock($fp, LOCK_EX);
        fwrite($fp, $content);
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    } else {
        return false;
    }
}

/**
 * safe_file_get_contents() 用共享锁模式打开文件并读取内容，可以避免在并发写入造成的读取不完整问题
 *
 * @param string $filename
 *
 * @return mixed
 */
function safe_file_get_contents($filename)
{
    $fp = fopen($filename, 'rb');
    if ($fp) {
        flock($fp, LOCK_SH);
        clearstatcache();
        $filesize = filesize($filename);
        if ($filesize > 0) {
            $data = fread($fp, $filesize);
        } else {
            $data = false;
        }
        flock($fp, LOCK_UN);
        fclose($fp);
        return $data;
    } else {
        return false;
    }
}

if (!function_exists('file_put_contents'))
{
    function file_put_contents($filename, & $content)
    {
        return safe_file_put_contents($filename, $content);
    }
}

/**
 * 调试和错误处理相关的全局函数
 */

/**
 * 设置异常拦截点
 *
 * @package Core
 */
function __TRY()
{
    static $point = 0;
    if (!isset($GLOBALS[G_FLEA_VAR]['FLEA_EXCEPTION_STACK']) ||
        !is_array($GLOBALS[G_FLEA_VAR]['FLEA_EXCEPTION_STACK']))
    {
        $GLOBALS[G_FLEA_VAR]['FLEA_EXCEPTION_STACK'] = [];
    }

    $point++;
    array_push($GLOBALS[G_FLEA_VAR]['FLEA_EXCEPTION_STACK'], $point);
}

/**
 * 返回抛出的异常，如果没有异常抛出，返回 false
 *
 * @package Core
 *
 * @return FLEA_Exception
 */
function __CATCH()
{
    if (!is_array($GLOBALS[G_FLEA_VAR]['FLEA_EXCEPTION_STACK'])) {
        return false;
    }
    $exception = array_pop($GLOBALS[G_FLEA_VAR]['FLEA_EXCEPTION_STACK']);
    if (!is_object($exception)) {
        $exception = false;
    }
    return $exception;
}

/**
 * 清除最后一个 __TRY() 异常捕获设置
 *
 * @package Core
 */
function __CANCEL_TRY()
{
    if (is_array($GLOBALS[G_FLEA_VAR]['FLEA_EXCEPTION_STACK'])) {
        array_pop($GLOBALS[G_FLEA_VAR]['FLEA_EXCEPTION_STACK']);
    }
}

/**
 * 判断是否是一个异常
 *
 * $type 参数用于判断异常是否是指定的类型。
 *
 * @package Core
 *
 * @param FLEA_Exception $exception
 * @param string $type
 */
function __IS_EXCEPTION($exception, $type = null)
{
    if (!is_object($exception) || !is_a($exception, 'FLEA_Exception')) {
        return false;
    }
    if (is_null($type)) {
        return true;
    } else {
        return strtoupper($type) == strtoupper(get_class($exception));
    }
}

/**
 * 设置新的异常处理例程，返回当前使用的异常处理例程
 *
 * 当抛出的异常没有任何 __TRY() 捕获时，将调用异常处理例程。FleaPHP 默认的
 * 异常处理例程会显示异常的详细信息，已经程序运行路径，帮助开发者定位错误。
 *
 * 用法：
 * <code>
 * // 保存现在使用的异常处理例程
 * global $prevExceptionHandler;
 * $prevExceptionHandler = __SET_EXCEPTION_HANDLER('app_exception_handler');
 *
 * function app_exception_handler(& $ex) {
 *     global $prevExceptionHandler;
 *
 *     if (is_a($ex, 'APP_Exception')) {
 *        // 处理该异常
 *        ...
 *     } else {
 *        // 调用原有的异常处理例程
 *        if ($prevExceptionHandler) {
 *            call_user_func_array($prevExceptionHandler, array(& $exception));
 *        }
 *     }
 * }
 * </code>
 *
 * 上面的代码设置了一个新的异常处理例程，同时可以在必要时调用原有的异常处理例程。
 * 虽然不强制要求开发者这样做，但参照上面的代码片段可以形成一个异常处理例程调用链。
 *
 * @package Core
 *
 * @param callback $callback
 *
 * @return mixed
 */
function __SET_EXCEPTION_HANDLER($callback)
{
    $config = FLEA_Config::getInstance();
    $current = $config->getExceptionHandler();
    $config->setExceptionHandler($callback);
    return $current;
}

/**
 * FleaPHP 默认的异常处理例程
 *
 * @package Core
 *
 * @param FLEA_Exception $ex
 */
function __FLEA_EXCEPTION_HANDLER($ex)
{
    if (!FLEA::getAppInf('displayErrors')) { exit; }
    if (FLEA::getAppInf('friendlyErrorsMessage')) {
        $language = FLEA::getAppInf('defaultLanguage');
        $language = preg_replace('/[^a-z0-9\-_]+/i', '', $language);

        $exclass = strtoupper(get_class($ex));
        $template = FLEA_DIR . "/_Errors/{$language}/{$exclass}.php";
        if (!file_exists($template)) {
            $template = FLEA_DIR . "/_Errors/{$language}/FLEA_EXCEPTION.php";
            if (!file_exists($template)) {
                $template = FLEA_DIR . "/_Errors/default/FLEA_EXCEPTION.php";
            }
        }
        include($template);
    } else {
        print_ex($ex);
    }
    exit;
}

/**
 * 打印异常的详细信息
 *
 * @package Core
 *
 * @param FLEA_Exception $ex
 * @param boolean $return 为 true 时返回输出信息，而不是直接显示
 */
function print_ex($ex, $return = false)
{
    $out = "exception '" . get_class($ex) . "'";
    if ($ex->getMessage() != '') {
        $out .= " with message '" . $ex->getMessage() . "'";
    }
    if (defined('DEPLOY_MODE') && DEPLOY_MODE != false) {
        $out .= ' in ' . basename($ex->getFile()) . ':' . $ex->getLine() . "\n\n";
    } else {
        $out .= ' in ' . $ex->getFile() . ':' . $ex->getLine() . "\n\n";
        $out .= $ex->getTraceAsString();
    }

    if ($return) { return $out; }

    if (ini_get('html_errors')) {
        echo nl2br(htmlspecialchars($out));
    } else {
        echo $out;
    }

    return '';
}

/**
 * 输出变量的内容，通常用于调试
 *
 * @package Core
 *
 * @param mixed $vars 要输出的变量
 * @param string $label
 * @param boolean $return
 */
function dump($vars, $label = '', $return = false)
{
    if (ini_get('html_errors')) {
        $content = "<pre>\n";
        if ($label != '') {
            $content .= "<strong>{$label} :</strong>\n";
        }
        $content .= htmlspecialchars(print_r($vars, true));
        $content .= "\n</pre>\n";
    } else {
        $content = $label . " :\n" . print_r($vars, true);
    }
    if ($return) { return $content; }
    echo $content;
    return null;
}

/**
 * 显示应用程序执行路径，通常用于调试
 *
 * @package Core
 *
 * @return string
 */
function dump_trace()
{
    $debug = debug_backtrace();
    $lines = '';
    $index = 0;
    for ($i = 0; $i < count($debug); $i++) {
        if ($i == 0) { continue; }
        $file = $debug[$i];
        if ($file['file'] == '') { continue; }
        if (substr($file['file'], 0, strlen(FLEA_DIR)) != FLEA_DIR) {
            $line = "#<strong>{$index} {$file['file']}({$file['line']}): </strong>";
        } else {
            $line = "#{$index} {$file['file']}({$file['line']}): ";
        }
        if (isset($file['class'])) {
            $line .= "{$file['class']}{$file['type']}";
        }
        $line .= "{$file['function']}(";
        if (isset($file['args']) && count($file['args'])) {
            foreach ($file['args'] as $arg) {
                $line .= gettype($arg) . ', ';
            }
            $line = substr($line, 0, -2);
        }
        $line .= ')';
        $lines .= $line . "\n";
        $index++;
    } // for
    $lines .= "#{$index} {main}\n";

    if (ini_get('html_errors')) {
        echo nl2br(str_replace(' ', '&nbsp;', $lines));
    } else {
        echo $lines;
    }
}

/**
 * 获取当前毫秒数（浮点数格式）
 *
 * @param mixed $time
 *
 * @return float
 */
function microtime_float($time = null)
{
    list($usec, $sec) = explode(' ', $time ? $time : microtime());
    return ((float)$usec + (float)$sec);
}

/**
 * 查询指定错误信息对应的消息文本
 *
 * 该函数会根据应用程序设置 'defaultLanguage' 载入不同语言的错误信息文件，
 * 然后根据错误代码查询错误消息文本，并返回查询结果。
 *
 * 注意，如果找不到指定语言的错误信息，会载入名为 default 的语言文件。
 *
 * 当 $appError 为 true 时，_ET() 会尝试在应用程序设置
 * 'languageFilesDir' 指定的目录中读取语言文件。
 *
 * @package Core
 *
 * @param int $errorCode
 * @param boolean $appError
 *
 * @return string
 */
function _ET($errorCode, $appError = false)
{
    static $message = [];

    $language = FLEA::getAppInf('defaultLanguage');
    $language = preg_replace('/[^a-z0-9\-_]+/i', '', $language);

    if (!isset($message[$language])) {
        if ($appError) {
            $filename = FLEA::getAppInf('languageFilesDir') . DS .
                $language . DS . 'ErrorMessage.php';
        } else {
            // 读取 FleaPHP 自带的错误信息列表
            $filename = FLEA_DIR . "/_Errors/{$language}/ErrorMessage.php";
        }
        if (!is_readable($filename)) {
            $filename = FLEA_DIR . '/_Errors/default/ErrorMessage.php';
        }
        $message[$language] = include($filename);
    }

    return isset($message[$language][$errorCode]) ?
        $message[$language][$errorCode] :
        '';
}

class FLEA_Exception extends Exception
{
    function __construct($message = '', $code = 0)
    {
        parent::__construct($message, $code);
    }
}
