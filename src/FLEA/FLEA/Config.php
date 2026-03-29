<?php

namespace FLEA;

/**
 * 应用程序配置管理（单例）
 *
 * 负责管理框架和应用的所有配置项。配置数据存储在 $appInf 数组中，
 * 支持合并、读取、设置等操作。
 *
 * 配置加载顺序：
 * 1. 框架默认配置（Config\Defaults）
 * 2. 应用配置文件（通过 mergeAppInf 合并）
 *
 * 用法示例：
 * ```php
 * // 获取配置单例
 * $config = \FLEA\Config::getInstance();
 *
 * // 合并应用配置
 * $config->mergeAppInf(['dbDSN' => 'mysql://localhost/blog']);
 *
 * // 获取配置项
 * $dsn = $config->getAppInf('dbDSN');
 *
 * // 获取嵌套配置值
 * $host = $config->getAppInfValue('dbDSN', 'host');
 *
 * // 设置配置项
 * $config->setAppInfValue('dbDSN', 'host', '192.168.1.100');
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class Config
{
    /**
     * @var ?self Config 单例实例
     */
    private static ?self $instance = null;

    /**
     * @var array 配置项数组
     */
    public array $appInf = [];

    /**
     * 构造函数
     *
     * 加载框架默认配置
     */
    private function __construct()
    {
        // 加载框架默认配置
        $this->appInf = \FLEA\Config\Defaults::$config;
    }

    /**
     * 阻止克隆实例
     */
    private function __clone() {}

    /**
     * 获取 Config 单例实例
     *
     * 用法示例：
     * ```php
     * $config = \FLEA\Config::getInstance();
     * $config->mergeAppInf(['key' => 'value']);
     * ```
     *
     * @return self Config 实例
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 获取配置项的值
     *
     * 用法示例：
     * ```php
     * // 获取配置项
     * $controller = $config->getAppInf('defaultController');
     *
     * // 带默认值
     * $timezone = $config->getAppInf('defaultTimezone', 'UTC');
     * ```
     *
     * @param string $option  配置项名称
     * @param mixed  $default 默认值（配置项不存在时返回）
     *
     * @return mixed 配置项的值
     */
    public function getAppInf(string $option, $default = null)
    {
        return $this->appInf[$option] ?? $default;
    }

    /**
     * 设置配置项
     *
     * 支持两种调用方式：
     * 1. 传入配置数组
     * 2. 传入配置项名称和值
     *
     * 用法示例：
     * ```php
     * // 方式 1：传入配置数组
     * $config->setAppInf(['key1' => 'value1', 'key2' => 'value2']);
     *
     * // 方式 2：传入单个配置项
     * $config->setAppInf('key1', 'value1');
     * ```
     *
     * @param string|array $option 配置项名称或配置数组
     * @param mixed        $data   配置值（当 $option 为字符串时有效）
     *
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
     * 获取嵌套配置项的值
     *
     * 用于获取二维数组形式的配置项中的子值。
     *
     * 用法示例：
     * ```php
     * // 获取 dbDSN 配置中的 'host' 值
     * $host = $config->getAppInfValue('dbDSN', 'host');
     * ```
     *
     * @param string $option   配置项名称（如 'dbDSN'）
     * @param string $keyname  子键名称（如 'host'）
     * @param mixed  $default  默认值
     *
     * @return mixed 配置子项的值
     */
    public function getAppInfValue(string $option, string $keyname, $default = null)
    {
        if (!isset($this->appInf[$option])) {
            $this->appInf[$option] = [];
        }
        return array_key_exists($keyname, $this->appInf[$option])
            ? $this->appInf[$option][$keyname]
            : ($this->appInf[$option][$keyname] = $default);
    }

    /**
     * 设置嵌套配置项的值
     *
     * 用法示例：
     * ```php
     * $config->setAppInfValue('dbDSN', 'host', '192.168.1.100');
     * ```
     *
     * @param string $option   配置项名称
     * @param string $keyname  子键名称
     * @param mixed  $value    要设置的值
     *
     * @return void
     */
    public function setAppInfValue(string $option, string $keyname, $value): void
    {
        $this->appInf[$option][$keyname] = $value;
    }

    /**
     * 合并配置数组
     *
     * 将传入的配置数组与现有配置合并。新配置会覆盖已有配置。
     *
     * 用法示例：
     * ```php
     * $config->mergeAppInf([
     *     'dbDSN' => 'mysql://localhost/blog',
     *     'defaultController' => 'Post'
     * ]);
     * ```
     *
     * @param array $config 要合并的配置数组
     *
     * @return void
     */
    public function mergeAppInf(array $config): void
    {
        $this->appInf = array_merge($this->appInf, $config);
    }

    /**
     * 获取配置项的值（向后兼容）
     *
     * 直接按 key 取值，功能等同 getAppInf()。
     * 为向后兼容旧代码而保留。
     *
     * 用法示例：
     * ```php
     * $value = $config->get('dbDSN');
     * ```
     *
     * @param string $key     配置项名称
     * @param mixed  $default 默认值
     *
     * @return mixed 配置项的值
     *
     * @see    getAppInf()
     */
    public function get(string $key, $default = null)
    {
        return $this->appInf[$key] ?? $default;
    }
}
