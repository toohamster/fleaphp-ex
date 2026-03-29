<?php

namespace FLEA\Cache;

/**
 * Redis 缓存，实现 PSR-16 CacheInterface
 *
 * 需要 PHP redis 扩展（pecl install redis）
 *
 * 配置示例：
 *   'cacheProvider'  => \FLEA\RedisCache::class,
 *   'redisHost'      => '127.0.0.1',
 *   'redisPort'      => 6379,
 *   'redisPassword'  => '',
 *   'redisDb'        => 0,
 *   'redisPrefix'    => 'flea:',
 *   'cacheTtl'       => 3600,
 */
class RedisCache implements \Psr\SimpleCache\CacheInterface
{
    private \Redis $redis;
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
