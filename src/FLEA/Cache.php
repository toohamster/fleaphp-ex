<?php

namespace FLEA;

/**
 * 缓存门面
 *
 * 通过配置 cacheProvider 无缝切换缓存驱动：
 * - null（默认）→ \FLEA\Cache\FileCache（文件缓存）
 * - \FLEA\Cache\RedisCache::class → Redis 缓存
 *
 * 直接使用 PSR-16 接口操作缓存：
 * ```php
 * // 获取缓存驱动
 * $cache = \FLEA\Cache::provider();
 *
 * // 获取缓存数据
 * $data = $cache->get('key');
 *
 * // 设置缓存（带有效期）
 * $cache->set('key', $value, 3600);
 *
 * // 删除缓存
 * $cache->delete('key');
 *
 * // 清空所有缓存
 * $cache->clear();
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 * @see     \Psr\SimpleCache\CacheInterface
 * @see     \FLEA\Cache\FileCache
 * @see     \FLEA\Cache\RedisCache
 */
class Cache
{
    /**
     * 获取缓存驱动实例
     *
     * 根据配置中的 cacheProvider 返回对应的缓存驱动实例。
     * 未配置时默认使用 FileCache（文件缓存）。
     * 返回的实例实现 PSR-16 CacheInterface 接口。
     *
     * 用法示例：
     * ```php
     * // 获取默认缓存驱动
     * $cache = \FLEA\Cache::provider();
     *
     * // 获取缓存数据
     * $data = $cache->get('user_123');
     *
     * // 设置缓存（有效期 3600 秒）
     * $cache->set('user_123', $userData, 3600);
     *
     * // 删除缓存
     * $cache->delete('user_123');
     * ```
     *
     * @return \Psr\SimpleCache\CacheInterface PSR-16 缓存驱动实例
     *
     * @see    \Psr\SimpleCache\CacheInterface
     */
    public static function provider(): \Psr\SimpleCache\CacheInterface
    {
        $providerClass = Config::getInstance()->getAppInf('cacheProvider')
            ?? \FLEA\Cache\FileCache::class;

        return \FLEA\Container::getInstance()->singleton($providerClass);
    }
}
