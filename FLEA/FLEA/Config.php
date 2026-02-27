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
namespace FLEA;

class Config
{
    /**
     * 单例实例
     *
     * @var Config
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
     * 私有构造函数，防止外部实例化
     */
    private function __construct()
    {
        $this->appInf = [];
        $this->objects = [];
        $this->dbo = [];
    }

    /**
     * 获取单例实例
     *
     * @return Config
     */
    public static function getInstance(): self
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
        return $this->appInf[$option] ?? $default;
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
     * @throws \FLEA\Exception\ExistsKeyName 如果对象名称已存在
     * @throws \FLEA\Exception\TypeMismatch 如果参数不是对象
     */
    public function registerObject($obj, ?string $name = null)
    {
        if (!is_object($obj)) {
            throw new \FLEA\Exception\TypeMismatch($obj, 'object', gettype($obj));
        }

        if (is_null($name)) {
            $name = get_class($obj);
        }

        if (isset($this->objects[$name])) {
            throw new \FLEA\Exception\ExistsKeyName($name);
        }

        $this->objects[$name] = $obj;
        return $obj;
    }

    /**
     * 从对象容器获取对象
     *
     * @param string|null $name 对象名称，为 null 时返回所有对象
     * @return object|array|null
     * @throws Exception\NotExistsKeyName 当对象不存在时
     */
    public function getRegistry(?string $name = null)
    {
        if (is_null($name)) {
            return $this->objects;
        }
        if (isset($this->objects[$name]) && is_object($this->objects[$name])) {
            return $this->objects[$name];
        }
        throw new \FLEA\Exception\NotExistsKeyName($name);
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
        return $this->dbo[$dsnid] ?? null;
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
     * 防止克隆
     */
    private function __clone()
    {
        // 私有方法，防止克隆
    }
}
