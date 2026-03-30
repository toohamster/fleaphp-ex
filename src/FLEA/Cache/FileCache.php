<?php

namespace FLEA\Cache;

/**
 * 文件缓存实现类
 *
 * 实现 PSR-16 CacheInterface 接口，将缓存数据保存到文件系统。
 * 使用 CRC32 校验确保数据完整性，带过期时间支持。
 *
 * 主要功能：
 * - PSR-16 标准接口实现
 * - 自动过期支持
 * - 数据完整性校验（CRC32）
 * - 单例模式
 *
 * 缓存文件格式：
 * - 前缀 16 字节：'<?php die(); ?> '
 * - CRC32 校验 32 字节
 * - 序列化数据
 *
 * 用法示例：
 * ```php
 * $cache = FileCache::getInstance();
 *
 * // 设置缓存
 * $cache->set('key', 'value', 3600);  // 1 小时过期
 *
 * // 获取缓存
 * $value = $cache->get('key', 'default');
 *
 * // 批量操作
 * $cache->setMultiple(['k1' => 'v1', 'k2' => 'v2']);
 * $values = $cache->getMultiple(['k1', 'k2']);
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 * @see     \Psr\SimpleCache\CacheInterface
 */
class FileCache implements \Psr\SimpleCache\CacheInterface
{
    /**
     * @var self|null 单例实例
     */
    private static ?self $instance = null;

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function getCacheFile(string $key): string
    {
        $cacheDir = \FLEA\Config::getInstance()->getAppInf('internalCacheDir');
        if (!$cacheDir) {
            throw new \FLEA\Exception\CacheDisabled($cacheDir);
        }
        return $cacheDir . DS . md5($key) . '.php';
    }

    private function readFile(string $file)
    {
        if (!file_exists($file)) { return null; }
        $data = file_get_contents($file);
        if ($data === false) { return null; }
        $hash = substr($data, 16, 32);
        $payload = substr($data, 48);
        if (sprintf('% 32d', crc32($payload)) !== $hash) { return null; }
        return unserialize($payload);
    }

    private function writeFile(string $file, $data): bool
    {
        $payload = serialize($data);
        $content = '<?php die(); ?> ' . sprintf('% 32d', crc32($payload)) . $payload;
        return safe_file_put_contents($file, $content);
    }

    private function ttlToExpiry($ttl): ?int
    {
        if ($ttl === null) { return null; }
        if ($ttl instanceof \DateInterval) {
            return (new \DateTime())->add($ttl)->getTimestamp();
        }
        return $ttl > 0 ? time() + (int)$ttl : 0;
    }

    public function get($key, $default = null)
    {
        $file = $this->getCacheFile($key);
        $entry = $this->readFile($file);
        if ($entry === null) { return $default; }
        if ($entry['expiry'] !== null && time() > $entry['expiry']) {
            unlink($file);
            return $default;
        }
        return $entry['value'];
    }

    public function set($key, $value, $ttl = null): bool
    {
        if ($ttl === null) {
            $ttl = \FLEA\Config::getInstance()->getAppInf('cacheTtl');
        }
        $entry = ['value' => $value, 'expiry' => $this->ttlToExpiry($ttl)];
        return $this->writeFile($this->getCacheFile($key), $entry);
    }

    public function delete($key): bool
    {
        $file = $this->getCacheFile($key);
        return file_exists($file) ? unlink($file) : true;
    }

    public function clear(): bool
    {
        $cacheDir = \FLEA\Config::getInstance()->getAppInf('internalCacheDir');
        foreach (glob($cacheDir . DS . '*.php') as $file) {
            unlink($file);
        }
        return true;
    }

    public function getMultiple($keys, $default = null): iterable
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
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
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return true;
    }

    public function has($key): bool
    {
        return $this->get($key, $this) !== $this;
    }
}
