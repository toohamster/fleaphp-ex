<?php

namespace FLEA\Cache;

/**
 * Redis 缓存实现类
 *
 * 实现 PSR-16 CacheInterface 接口，使用 Redis 作为缓存存储。
 * 支持键前缀、密码认证、数据库选择等配置。
 *
 * 需要 PHP redis 扩展（pecl install redis）。
 *
 * 配置项（通过 Config 设置）：
 * - cacheProvider: \FLEA\Cache\RedisCache::class
 * - redisHost: Redis 主机，默认 '127.0.0.1'
 * - redisPort: Redis 端口，默认 6379
 * - redisPassword: Redis 密码，默认空
 * - redisDb: Redis 数据库编号，默认 0
 * - redisPrefix: 键前缀，默认 'flea:'
 * - cacheTtl: 默认缓存时间（秒），默认 3600
 *
 * 用法示例：
 * ```php
 * $cache = new RedisCache();
 *
 * // 设置缓存
 * $cache->set('key', 'value', 3600);
 *
 * // 获取缓存
 * $value = $cache->get('key', 'default');
 *
 * // 检查是否存在
 * if ($cache->has('key')) {
 *     // key 存在
 * }
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 * @see     \Psr\SimpleCache\CacheInterface
 */
class RedisCache implements \Psr\SimpleCache\CacheInterface
{
    /**
     * @var \Redis Redis 连接实例
     */
    private \Redis $redis;

    /**
     * @var string 键前缀
     */
    private string $prefix;

    public function __construct()
    {
        if (!extension_loaded('redis')) {
            throw new \RuntimeException('PHP redis extension is not loaded.');
        }
        $config = \FLEA\Config::getInstance();
        $this->prefix = $config->getAppInf('redisPrefix') ?? 'flea:';

        $this->redis = new \Redis();
        $this->redis->connect(
            $config->getAppInf('redisHost') ?? '127.0.0.1',
            (int)($config->getAppInf('redisPort') ?? 6379)
        );
        $password = $config->getAppInf('redisPassword');
        if ($password) {
            $this->redis->auth($password);
        }
        $this->redis->select((int)($config->getAppInf('redisDb') ?? 0));
    }

    private function key(string $key): string
    {
        return $this->prefix . $key;
    }

    private function ttl($ttl): int
    {
        if ($ttl === null) {
            $ttl = \FLEA\Config::getInstance()->getAppInf('cacheTtl');
        }
        if ($ttl instanceof \DateInterval) {
            return (int)(new \DateTime())->add($ttl)->getTimestamp() - time();
        }
        return $ttl === null ? 0 : (int)$ttl;
    }

    public function get($key, $default = null)
    {
        $value = $this->redis->get($this->key($key));
        return $value === false ? $default : unserialize($value);
    }

    public function set($key, $value, $ttl = null): bool
    {
        $seconds = $this->ttl($ttl);
        $serialized = serialize($value);
        return $seconds > 0
            ? $this->redis->setex($this->key($key), $seconds, $serialized)
            : (bool)$this->redis->set($this->key($key), $serialized);
    }

    public function delete($key): bool
    {
        return $this->redis->del($this->key($key)) >= 0;
    }

    public function clear(): bool
    {
        $keys = $this->redis->keys($this->prefix . '*');
        return empty($keys) || $this->redis->del($keys) >= 0;
    }

    public function getMultiple($keys, $default = null): iterable
    {
        $redisKeys = array_map(fn($k) => $this->key($k), (array)$keys);
        $values = $this->redis->mGet($redisKeys);
        $result = [];
        foreach ((array)$keys as $i => $key) {
            $result[$key] = $values[$i] !== false ? unserialize($values[$i]) : $default;
        }
        return $result;
    }

    public function setMultiple($values, $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) { return false; }
        }
        return true;
    }

    public function deleteMultiple($keys): bool
    {
        $redisKeys = array_map(fn($k) => $this->key($k), (array)$keys);
        return $this->redis->del($redisKeys) >= 0;
    }

    public function has($key): bool
    {
        return (bool)$this->redis->exists($this->key($key));
    }
}
