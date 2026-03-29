<?php

namespace FLEA;

/**
 * 环境检测工具类
 *
 * 用于检测 PHP 环境特性，结果缓存避免重复检测。
 * 提供统一的环境判断方法。
 *
 * 主要功能：
 * - PHP 扩展支持检测（mbstring、Redis、GD、cURL、OpenSSL）
 * - 运行环境检测（CLI、Web）
 * - 应用环境检测（开发、生产、测试等）
 * - PHP 版本检测
 *
 * 用法示例：
 * ```php
 * // 扩展支持检测
 * if (\FLEA\Env::hasMbstring()) { }
 * if (\FLEA\Env::hasRedis()) { }
 * if (\FLEA\Env::hasGd()) { }
 *
 * // 运行环境检测
 * if (\FLEA\Env::isCli()) { }
 * if (\FLEA\Env::isWeb()) { }
 *
 * // 应用环境检测
 * if (\FLEA\Env::isProd()) { }        // 是否生产环境
 * if (\FLEA\Env::isEnv('development')) { }  // 是否开发环境
 *
 * // PHP 版本检测
 * $version = \FLEA\Env::phpVersion();
 * if (\FLEA\Env::phpVersionAtLeast('7.4.0')) { }
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class Env
{
    /**
     * @var array 已缓存的检测结果
     */
    private static array $cache = [];

    /**
     * 检测是否支持 mbstring 扩展
     *
     * @return bool 支持返回 true，否则返回 false
     */
    public static function hasMbstring(): bool
    {
        return self::check('mbstring', function() {
            return function_exists('mb_strlen');
        });
    }

    /**
     * 检测是否支持 Redis 扩展
     *
     * @return bool 支持返回 true，否则返回 false
     */
    public static function hasRedis(): bool
    {
        return self::check('redis', function() {
            return class_exists('Redis', false);
        });
    }

    /**
     * 检测是否支持 GD 扩展
     *
     * @return bool 支持返回 true，否则返回 false
     */
    public static function hasGd(): bool
    {
        return self::check('gd', function() {
            return function_exists('gd_info');
        });
    }

    /**
     * 检测是否支持 cURL 扩展
     *
     * @return bool 支持返回 true，否则返回 false
     */
    public static function hasCurl(): bool
    {
        return self::check('curl', function() {
            return function_exists('curl_version');
        });
    }

    /**
     * 检测是否支持 OpenSSL 扩展
     *
     * @return bool 支持返回 true，否则返回 false
     */
    public static function hasOpenssl(): bool
    {
        return self::check('openssl', function() {
            return extension_loaded('openssl');
        });
    }

    /**
     * 检测当前是否为 CLI 环境
     *
     * @return bool CLI 环境返回 true，否则返回 false
     */
    public static function isCli(): bool
    {
        return self::check('is_cli', function() {
            return PHP_SAPI === 'cli';
        });
    }

    /**
     * 检测当前是否为 Web 环境
     *
     * @return bool Web 环境返回 true，否则返回 false
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
     * 根据 .env 中的 APP_ENV 值判断当前运行环境。
     *
     * 用法示例：
     * ```php
     * Env::isEnv('production')   // 是否生产环境
     * Env::isEnv('development')  // 是否开发环境
     * Env::isEnv('test')         // 是否测试环境
     * Env::isEnv('local')        // 是否本地环境
     * ```
     *
     * @param string $env 环境名称（如 'production', 'development', 'test'）
     *
     * @return bool 匹配返回 true，否则返回 false
     */
    public static function isEnv(string $env): bool
    {
        return self::check('is_env_' . $env, function() use ($env) {
            return env('APP_ENV') === $env;
        });
    }

    /**
     * 检测是否为生产环境
     *
     * @return bool 生产环境返回 true，否则返回 false
     */
    public static function isProd(): bool
    {
        return self::isEnv('production');
    }

    /**
     * 获取 PHP 版本号
     *
     * @return string PHP 版本号（如 '7.4.32'）
     */
    public static function phpVersion(): string
    {
        return PHP_VERSION;
    }

    /**
     * 检测 PHP 版本是否满足要求
     *
     * 用法示例：
     * ```php
     * if (\FLEA\Env::phpVersionAtLeast('7.4.0')) {
     *     // PHP 版本 >= 7.4.0
     * }
     * ```
     *
     * @param string $version 最低版本要求（如 '7.4.0'）
     *
     * @return bool 满足要求返回 true，否则返回 false
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
     * 使用缓存避免重复检测，提高性能。
     *
     * @param string   $key     检测项标识
     * @param callable $checker 检测回调
     *
     * @return mixed 检测结果
     */
    private static function check(string $key, callable $checker)
    {
        if (!isset(self::$cache[$key])) {
            self::$cache[$key] = $checker();
        }
        return self::$cache[$key];
    }
}
