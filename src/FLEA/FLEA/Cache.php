<?php

namespace FLEA;

/**
 * 缓存门面
 *
 * 通过配置 cacheProvider 无缝切换缓存驱动：
 *   null（默认）→ FLEA\Cache\FileCache
 *   \FLEA\Cache\RedisCache::class → Redis
 *
 * 直接使用 PSR-16 接口：
 *   Cache::provider()->get('key');
 *   Cache::provider()->set('key', $value, 3600);
 */
class Cache
{
    /**
     * 返回当前配置的缓存驱动（PSR-16）
     */
    public static function provider(): \Psr\SimpleCache\CacheInterface
    {
        $providerClass = Config::getInstance()->getAppInf('cacheProvider')
            ?? \FLEA\Cache\FileCache::class;

        return \FLEA\Container::getInstance()->singleton($providerClass);
    }
}
