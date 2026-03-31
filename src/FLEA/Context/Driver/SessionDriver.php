<?php

namespace FLEA\Context\Driver;

use FLEA\Context\DriverInterface;

/**
 * Session 存储驱动
 *
 * 使用 PHP 原生 Session 作为上下文存储后端。
 * 适用于传统 Web 应用场景。
 *
 * @package FLEA
 * @subpackage Context\Driver
 * @author toohamster
 * @version 2.0.0
 */
class SessionDriver implements DriverInterface
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
    }

    /**
     * 获取值
     *
     * @param string $key 键名
     * @param mixed $default 默认值
     *
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * 设置值
     *
     * @param string $key 键名
     * @param mixed $value 值
     * @param int|null $ttl 过期时间（Session 忽略此参数）
     *
     * @return bool
     */
    public function set(string $key, $value, ?int $ttl = null): bool
    {
        $_SESSION[$key] = $value;
        return true;
    }

    /**
     * 删除值
     *
     * @param string $key 键名
     *
     * @return bool
     */
    public function remove(string $key): bool
    {
        unset($_SESSION[$key]);
        return true;
    }

    /**
     * 检查键是否存在
     *
     * @param string $key 键名
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }
}
