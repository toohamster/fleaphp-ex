<?php

namespace FLEA;

/**
 * 环境检测工具类
 *
 * 用于检测 PHP 环境特性，结果缓存避免重复检测
 *
 */
class Env
{
    /** @var array 已缓存的检测结果 */
    private static array $cache = [];

    /**
     * 检测是否支持 mbstring 扩展
     */
    public static function hasMbstring(): bool
    {
        return self::check('mbstring', function() {
            return function_exists('mb_strlen');
        });
    }

    /**
     * 检测是否支持 Redis 扩展
     */
    public static function hasRedis(): bool
    {
        return self::check('redis', function() {
            return class_exists('Redis', false);
        });
    }

    /**
     * 检测是否支持 GD 扩展
     */
    public static function hasGd(): bool
    {
        return self::check('gd', function() {
            return function_exists('gd_info');
        });
    }

    /**
     * 检测是否支持 cURL 扩展
     */
    public static function hasCurl(): bool
    {
        return self::check('curl', function() {
            return function_exists('curl_version');
        });
    }

    /**
     * 检测是否支持 OpenSSL 扩展
     */
    public static function hasOpenssl(): bool
    {
        return self::check('openssl', function() {
            return extension_loaded('openssl');
        });
    }

    /**
     * 检测当前是否为 CLI 环境
     */
    public static function isCli(): bool
    {
        return self::check('is_cli', function() {
            return PHP_SAPI === 'cli';
        });
    }

    /**
     * 检测当前是否为 Web 环境
     */
    public static function isWeb(): bool
    {
        return self::check('is_web', function() {
            return !self::isCli();
        });
    }

    /**
     * 检测当前是否为指定环境
     *
     * 用法：
     *   Env::isEnv('production')  // 是否生产环境
     *   Env::isEnv('development') // 是否开发环境
     *   Env::isEnv('test')        // 是否测试环境
     *
     * @param string $env 环境名称，如 'production', 'development', 'test'
     * @return bool
     */
    public static function isEnv(string $env): bool
    {
        return self::check('is_env_' . $env, function() use ($env) {
            return env('APP_ENV') === $env;
        });
    }

    /**
     * 检测是否为生产环境
     */
    public static function isProd(): bool
    {
        return self::isEnv('production');
    }

    /**
     * 获取 PHP 版本号
     */
    public static function phpVersion(): string
    {
        return PHP_VERSION;
    }

    /**
     * 检测 PHP 版本是否满足要求
     *
     * @param string $version 最低版本要求，如 '7.4.0'
     */
    public static function phpVersionAtLeast(string $version): bool
    {
        return self::check('php_version_' . $version, function() use ($version) {
            return version_compare(PHP_VERSION, $version, '>=');
        });
    }

    /**
     * 通用检测方法
     *
     * @param string $key 检测项标识
     * @param callable $checker 检测回调
     * @return mixed
     */
    private static function check(string $key, callable $checker)
    {
        if (!isset(self::$cache[$key])) {
            self::$cache[$key] = $checker();
        }
        return self::$cache[$key];
    }
}
