<?php

namespace FLEA\Context\Driver;

use FLEA\Context\DriverInterface;

/**
 * Redis 存储驱动
 *
 * 使用 Redis 作为上下文存储后端。
 * 适用于微服务、分布式应用场景。
 *
 * @package FLEA
 * @subpackage Context\Driver
 * @author toohamster
 * @version 2.0.0
 */
class RedisDriver implements DriverInterface
{
    /**
     * Redis 实例
     *
     * @var \Redis
     */
    private \Redis $redis;

    /**
     * 键前缀（用于隔离不同应用的数据）
     *
     * @var string
     */
    private string $prefix;

    /**
     * 构造函数
     *
     * @param array $config Redis 配置
     *                      - host: 主机地址（默认 127.0.0.1）
     *                      - port: 端口（默认 6379）
     *                      - prefix: 键前缀（默认 fleaphp:context:）
     */
    public function __construct(array $config = [])
    {
        $this->redis = new \Redis();
        $this->redis->connect(
            $config['host'] ?? '127.0.0.1',
            $config['port'] ?? 6379
        );

        if (!empty($config['password'])) {
            $this->redis->auth($config['password']);
        }

        $this->prefix = $config['prefix'] ?? 'fleaphp:context:';
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
        $value = $this->redis->get($this->prefix . $key);
        if ($value === false) {
            return $default;
        }
        return unserialize($value);
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
        $data = serialize($value);
        if ($ttl !== null) {
            return $this->redis->setEx($this->prefix . $key, $ttl, $data);
        }
        return $this->redis->set($this->prefix . $key, $data);
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
        return $this->redis->del($this->prefix . $key) > 0;
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
        return $this->redis->exists($this->prefix . $key);
    }
}
