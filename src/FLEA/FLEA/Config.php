<?php

namespace FLEA;

/**
 * 应用程序配置管理（单例）
 *
 * 只负责配置的读写，不再承担容器或数据库管理职责。
 */
class Config
{
    private static ?self $instance = null;

    public array $appInf = [];

    private function __construct()
    {
        // 加载框架默认配置
        $this->appInf = \FLEA\Config\Defaults::$config;
    }
    private function __clone() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getAppInf(string $option, $default = null)
    {
        return $this->appInf[$option] ?? $default;
    }

    public function setAppInf($option, $data = null): void
    {
        if (is_array($option)) {
            $this->appInf = array_merge($this->appInf, $option);
        } else {
            $this->appInf[$option] = $data;
        }
    }

    public function getAppInfValue(string $option, string $keyname, $default = null)
    {
        if (!isset($this->appInf[$option])) {
            $this->appInf[$option] = [];
        }
        return array_key_exists($keyname, $this->appInf[$option])
            ? $this->appInf[$option][$keyname]
            : ($this->appInf[$option][$keyname] = $default);
    }

    public function setAppInfValue(string $option, string $keyname, $value): void
    {
        $this->appInf[$option][$keyname] = $value;
    }

    public function mergeAppInf(array $config): void
    {
        $this->appInf = array_merge($this->appInf, $config);
    }

    /** 向后兼容：直接按 key 取值（等同 getAppInf） */
    public function get(string $key, $default = null)
    {
        return $this->appInf[$key] ?? $default;
    }
}
