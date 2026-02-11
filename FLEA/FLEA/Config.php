<?php

/**
 * FLEA_Config 类 - FleaPHP 框架配置管理类
 *
 * 该类使用单例模式管理框架的所有配置，替代了旧版本中使用的全局变量 $GLOBALS[G_FLEA_VAR]。
 * 在 PHP7+ 中推荐使用面向对象的方式来管理配置，而不是使用全局变量。
 *
 * @package Core
 * @version 1.0
 */
class FLEA_Config
{
    /**
     * 单例实例
     *
     * @var FLEA_Config
     */
    private static $_instance = null;

    /**
     * 应用程序配置
     *
     * @var array
     */
    public $appInf = [];

    /**
     * 对象实例容器
     *
     * @var array
     */
    public $objects = [];

    /**
     * 数据库访问对象
     *
     * @var array
     */
    public $dbo = [];

    /**
     * 类文件搜索路径
     *
     * @var array
     */
    public $classPath = [];

    /**
     * 异常堆栈
     *
     * @var array
     */
    public $exceptionStack = [];

    /**
     * 异常处理器
     *
     * @var callable|null
     */
    public $exceptionHandler = null;

    /**
     * 私有构造函数，防止外部实例化
     */
    private function __construct()
    {
        $this->classPath = [];
        $this->appInf = [];
        $this->objects = [];
        $this->dbo = [];
        $this->exceptionStack = [];
        $this->exceptionHandler = null;
    }

    /**
     * 获取单例实例
     *
     * @return FLEA_Config
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * 获取应用程序配置值
     *
     * @param string $option 配置项名称
     * @param mixed $default 默认值
     * @return mixed
     */
    public function getAppInf(string $option, $default = null)
    {
        return isset($this->appInf[$option]) ? $this->appInf[$option] : $default;
    }

    /**
     * 设置应用程序配置值
     *
     * @param string|array $option 配置项名称或配置数组
     * @param mixed $data 配置值
     * @return void
     */
    public function setAppInf($option, $data = null): void
    {
        if (is_array($option)) {
            $this->appInf = array_merge($this->appInf, $option);
        } else {
            $this->appInf[$option] = $data;
        }
    }

    /**
     * 获取应用程序配置值的某个键
     *
     * @param string $option 配置项名称
     * @param string $keyname 键名
     * @param mixed $default 默认值
     * @return mixed
     */
    public function getAppInfValue(string $option, string $keyname, $default = null)
    {
        if (!isset($this->appInf[$option])) {
            $this->appInf[$option] = [];
        }
        if (array_key_exists($keyname, $this->appInf[$option])) {
            return $this->appInf[$option][$keyname];
        } else {
            $this->appInf[$option][$keyname] = $default;
            return $default;
        }
    }

    /**
     * 设置应用程序配置值的某个键
     *
     * @param string $option 配置项名称
     * @param string $keyname 键名
     * @param mixed $value 值
     * @return void
     */
    public function setAppInfValue(string $option, string $keyname, $value): void
    {
        if (!isset($this->appInf[$option])) {
            $this->appInf[$option] = [];
        }
        $this->appInf[$option][$keyname] = $value;
    }

    /**
     * 合并应用程序配置
     *
     * @param array $config 要合并的配置数组
     * @return void
     */
    public function mergeAppInf(array $config): void
    {
        $this->appInf = array_merge($this->appInf, $config);
    }

    /**
     * 注册对象到对象容器
     *
     * @param object $obj 对象实例
     * @param string|null $name 对象名称，默认使用类名
     * @return object
     * @throws Exception 如果参数不是对象
     */
    public function registerObject(object $obj, ?string $name = null): object
    {
        if (!is_object($obj)) {
            throw new Exception('First parameter must be an object');
        }

        if (is_null($name)) {
            $name = get_class($obj);
        }

        if (isset($this->objects[$name])) {
            throw new Exception("Object with name '{$name}' already exists");
        }

        $this->objects[$name] = $obj;
        return $obj;
    }

    /**
     * 从对象容器获取对象
     *
     * @param string|null $name 对象名称，为 null 时返回所有对象
     * @return object|array|null
     */
    public function getRegistry(?string $name = null)
    {
        if (is_null($name)) {
            return $this->objects;
        }
        if (isset($this->objects[$name]) && is_object($this->objects[$name])) {
            return $this->objects[$name];
        }
        return null;
    }

    /**
     * 检查对象是否已注册
     *
     * @param string $name 对象名称
     * @return bool
     */
    public function isRegistered(string $name): bool
    {
        return isset($this->objects[$name]);
    }

    /**
     * 注册数据库访问对象
     *
     * @param object $dbo 数据库访问对象
     * @param string $dsnid 数据源ID
     * @return void
     */
    public function registerDbo(object $dbo, string $dsnid): void
    {
        $this->dbo[$dsnid] = $dbo;
    }

    /**
     * 获取数据库访问对象
     *
     * @param string $dsnid 数据源ID
     * @return object|null
     */
    public function getDbo(string $dsnid): ?object
    {
        return isset($this->dbo[$dsnid]) ? $this->dbo[$dsnid] : null;
    }

    /**
     * 检查数据库访问对象是否存在
     *
     * @param string $dsnid 数据源ID
     * @return bool
     */
    public function hasDbo(string $dsnid): bool
    {
        return isset($this->dbo[$dsnid]);
    }

    /**
     * 添加类文件搜索路径
     *
     * @param string $dir 目录路径
     * @return void
     */
    public function addClassPath(string $dir): void
    {
        if (array_search($dir, $this->classPath, true)) {
            return;
        }
        if (DIRECTORY_SEPARATOR == '/') {
            $dir = str_replace('\\', DIRECTORY_SEPARATOR, $dir);
        } else {
            $dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
        }
        $this->classPath[] = $dir;
    }

    /**
     * 获取类文件搜索路径
     *
     * @return array
     */
    public function getClassPath(): array
    {
        return $this->classPath;
    }

    /**
     * 获取异常处理器
     *
     * @return callable|null
     */
    public function getExceptionHandler(): ?callable
    {
        return $this->exceptionHandler;
    }

    /**
     * 设置异常处理器
     *
     * @param callable $callback 异常处理回调函数
     * @return callable|null 返回之前的异常处理器
     */
    public function setExceptionHandler($callback): ?callable
    {
        $current = $this->exceptionHandler;
        $this->exceptionHandler = $callback;
        return $current;
    }

    /**
     * 推入异常到堆栈
     *
     * @param mixed $exception 异常对象或点标记
     * @return void
     */
    public function pushException($exception): void
    {
        if (!is_array($this->exceptionStack)) {
            $this->exceptionStack = [];
        }
        array_push($this->exceptionStack, $exception);
    }

    /**
     * 从堆栈弹出异常
     *
     * @return mixed
     */
    public function popException()
    {
        if (!is_array($this->exceptionStack)) {
            return null;
        }
        return array_pop($this->exceptionStack);
    }

    /**
     * 获取异常堆栈
     *
     * @return array
     */
    public function getExceptionStack(): array
    {
        return $this->exceptionStack;
    }

    /**
     * 检查异常堆栈是否为空
     *
     * @return bool
     */
    public function hasExceptionStack(): bool
    {
        return is_array($this->exceptionStack);
    }

    /**
     * 防止克隆
     */
    private function __clone()
    {
        // 私有方法，防止克隆
    }

    /**
     * 防止序列化
     */
    public function __sleep()
    {
        throw new Exception('Cannot serialize singleton');
    }

    /**
     * 防止反序列化
     */
    public function __wakeup()
    {
        throw new Exception('Cannot unserialize singleton');
    }
}
