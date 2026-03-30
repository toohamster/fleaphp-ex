<?php

namespace FLEA\Context;

/**
 * 上下文管理器
 *
 * 提供请求级别的状态管理服务，支持多种存储驱动和身份标识。
 *
 * 主要功能：
 * - 基于 Key-Value 的上下文数据存储
 * - 支持多种存储后端（Session、Redis、File）
 * - 支持多种身份标识（Session、JWT、API Key、Request ID）
 * - 自动数据隔离（基于身份标识前缀）
 *
 * 用法示例：
 * ```php
 * // 通过容器获取 Context 实例
 * $context = \FLEA::getSingleton(\FLEA\Context\Context::class);
 *
 * // 存储数据
 * $context->set('user_data', $userData, 3600);
 * $context->set('imgcode', 'abc123', 300);
 *
 * // 获取数据
 * $userData = $context->get('user_data');
 * $imgcode = $context->get('imgcode');
 *
 * // 检查数据是否存在
 * if ($context->has('user_data')) {
 *     // 数据存在
 * }
 *
 * // 删除数据
 * $context->remove('imgcode');
 * ```
 *
 * @package FLEA
 * @subpackage Context
 * @author toohamster
 * @version 2.0.0
 */
class Context
{
    /**
     * 存储驱动
     *
     * @var DriverInterface
     */
    private DriverInterface $driver;

    /**
     * 身份标识
     *
     * @var IdentityInterface
     */
    private IdentityInterface $identity;

    /**
     * 键前缀（用于数据隔离）
     *
     * @var string
     */
    private string $keyPrefix;

    /**
     * 构造函数
     *
     * @param DriverInterface $driver 存储驱动
     * @param IdentityInterface $identity 身份标识
     * @param string $keyPrefix 额外的键前缀（可选）
     */
    public function __construct(
        DriverInterface $driver,
        IdentityInterface $identity,
        string $keyPrefix = ''
    ) {
        $this->driver = $driver;
        $this->identity = $identity;
        $this->keyPrefix = $keyPrefix;
    }

    /**
     * 获取存储驱动
     *
     * @return DriverInterface
     */
    public function getDriver(): DriverInterface
    {
        return $this->driver;
    }

    /**
     * 获取身份标识
     *
     * @return IdentityInterface
     */
    public function getIdentity(): IdentityInterface
    {
        return $this->identity;
    }

    /**
     * 生成完整的存储键
     *
     * 格式：{keyPrefix}{identity}:{key}
     *
     * @param string $key 原始键名
     *
     * @return string 完整的键名
     */
    private function makeKey(string $key): string
    {
        $parts = [];

        if (!empty($this->keyPrefix)) {
            $parts[] = $this->keyPrefix;
        }

        $parts[] = $this->identity->getId();
        $parts[] = $key;

        return implode(':', $parts);
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
        return $this->driver->get($this->makeKey($key), $default);
    }

    /**
     * 设置值
     *
     * @param string $key 键名
     * @param mixed $value 值
     * @param int|null $ttl 过期时间（秒）
     *
     * @return bool
     */
    public function set(string $key, $value, ?int $ttl = null): bool
    {
        return $this->driver->set($this->makeKey($key), $value, $ttl);
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
        return $this->driver->remove($this->makeKey($key));
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
        return $this->driver->has($this->makeKey($key));
    }

    /**
     * 批量获取值
     *
     * @param array $keys 键名数组
     *
     * @return array 键值对数组
     */
    public function getMultiple(array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }
        return $result;
    }

    /**
     * 批量设置值
     *
     * @param array $data 键值对数组
     * @param int|null $ttl 过期时间（秒）
     *
     * @return bool
     */
    public function setMultiple(array $data, ?int $ttl = null): bool
    {
        $success = true;
        foreach ($data as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }
        return $success;
    }

    /**
     * 清空当前身份的所有数据
     *
     * 注意：此方法会删除当前身份标识下的所有数据，慎用！
     *
     * @return bool
     */
    public function clear(): bool
    {
        // 由于无法枚举所有键，此方法需要驱动支持
        // 默认不实现，由子类或驱动自行处理
        return false;
    }
}
