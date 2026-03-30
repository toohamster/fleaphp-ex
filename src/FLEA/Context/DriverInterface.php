<?php

namespace FLEA\Context;

/**
 * 上下文存储驱动接口
 *
 * 定义了上下文存储的基本操作，所有存储驱动必须实现此接口。
 *
 * @package FLEA
 * @subpackage Context
 * @author toohamster
 * @version 2.0.0
 */
interface DriverInterface
{
    /**
     * 获取值
     *
     * @param string $key 键名
     * @param mixed $default 默认值（键不存在时返回）
     *
     * @return mixed 存储的值或默认值
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * 设置值
     *
     * @param string $key 键名
     * @param mixed $value 要存储的值
     * @param int|null $ttl 过期时间（秒），null 表示永不过期
     *
     * @return bool 是否成功
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool;

    /**
     * 删除值
     *
     * @param string $key 键名
     *
     * @return bool 是否成功
     */
    public function remove(string $key): bool;

    /**
     * 检查键是否存在
     *
     * @param string $key 键名
     *
     * @return bool 是否存在
     */
    public function has(string $key): bool;
}
