<?php

namespace FLEA;

/**
 * 数据库连接管理
 *
 * 管理 DSN 解析和数据库连接实例池。
 * 负责根据 DSN 配置创建和复用数据库连接对象。
 *
 * 主要功能：
 * - DSN 解析：将字符串或数组格式的 DSN 解析为统一格式
 * - 连接池管理：相同 DSN 复用连接，避免重复创建
 * - 驱动加载：根据 DSN 中的 driver 自动加载对应驱动类
 *
 * 用法示例：
 * ```php
 * // 获取数据库管理单例
 * $db = \FLEA\Database::getInstance();
 *
 * // 获取默认连接（从配置的 dbDSN）
 * $dbo = $db->connect();
 *
 * // 获取指定 DSN 的连接
 * $dbo = $db->connect('mysql://root:pass@localhost/blog');
 *
 * // 解析 DSN 字符串
 * $config = $db->parseDSN('mysql://root:pass@localhost/blog');
 * // 返回：['driver'=>'mysql', 'host'=>'localhost', 'login'=>'root', ...]
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class Database
{
    /**
     * @var ?self Database 单例实例
     */
    private static ?self $instance = null;

    /**
     * @var \FLEA\Db\Driver\AbstractDriver[] 数据库连接池
     */
    private array $pool = [];

    /**
     * 构造函数
     */
    private function __construct() {}

    /**
     * 阻止克隆实例
     */
    private function __clone() {}

    /**
     * 获取 Database 单例实例
     *
     * 用法示例：
     * ```php
     * $db = \FLEA\Database::getInstance();
     * $dbo = $db->connect();
     * ```
     *
     * @return self Database 实例
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 获取数据库连接
     *
     * 根据 DSN 配置获取数据库连接对象。相同 DSN 会复用已有连接，
     * 避免重复创建。省略参数时使用配置中的默认 dbDSN。
     *
     * 用法示例：
     * ```php
     * // 获取默认连接
     * $dbo = $db->connect();
     *
     * // 获取指定 DSN 的连接
     * $dbo = $db->connect(1);
     * $dbo = $db->connect('mysql://localhost/blog');
     * ```
     *
     * @param int|string|array $dsn DSN 索引、DSN 字符串或配置数组
     *                              默认 0 表示使用配置中的默认连接
     *
     * @return \FLEA\Db\Driver\AbstractDriver 数据库驱动对象
     *
     * @throws \FLEA\Db\Exception\InvalidDSN 当 DSN 无效时抛出
     * @throws \FLEA\Exception\ExpectedClass 当驱动类不存在时抛出
     */
    public function connect($dsn = 0): \FLEA\Db\Driver\AbstractDriver
    {
        if ($dsn == 0) {
            $dsn = \FLEA\Config::getInstance()->get('dbDSN');
        }
        $dsn = $this->parseDSN($dsn);

        if (!is_array($dsn) || !isset($dsn['driver'])) {
            throw new \FLEA\Db\Exception\InvalidDSN($dsn);
        }

        $id = $dsn['id'];
        if (isset($this->pool[$id])) {
            return $this->pool[$id];
        }

        $className = '\\FLEA\\Db\\Driver\\' . ucfirst(strtolower($dsn['driver']));
        if (!class_exists($className, true)) {
            throw new \FLEA\Exception\ExpectedClass($className);
        }

        $dbo = new $className($dsn);
        $dbo->connect();
        $this->pool[$id] = $dbo;
        return $dbo;
    }

    /**
     * 解析 DSN 字符串或数组
     *
     * 将 DSN 字符串解析为数组格式，或补充数组 DSN 的缺失字段。
     * 支持格式：`driver://login:password@host:port/database?options`
     *
     * 用法示例：
     * ```php
     * // 解析字符串 DSN
     * $config = $db->parseDSN('mysql://root:pass@localhost/blog');
     * // 返回：['driver'=>'mysql', 'host'=>'localhost', 'login'=>'root', ...]
     *
     * // 解析数组 DSN（补充默认值）
     * $config = $db->parseDSN(['driver' => 'mysql', 'host' => 'localhost']);
     * ```
     *
     * @param string|array $dsn DSN 字符串或配置数组
     *
     * @return array|null 解析后的 DSN 配置数组，解析失败返回 null
     *
     * @see    \FLEA\Config::getAppInf()
     */
    public function parseDSN($dsn): ?array
    {
        $prefix = \FLEA\Config::getInstance()->getAppInf('dbTablePrefix');

        if (is_array($dsn)) {
            $dsn['host']     ??= '';
            $dsn['port']     ??= '';
            $dsn['login']    ??= '';
            $dsn['password'] ??= '';
            $dsn['database'] ??= '';
            $dsn['options']  ??= '';
            $dsn['prefix']   ??= $prefix;
            $dsn['schema']   ??= '';
        } else {
            $dsn = str_replace('@/', '@localhost/', $dsn);
            $parse = parse_url($dsn);
            if (empty($parse['scheme'])) {
                return null;
            }
            $dsn = [
                'host'     => $parse['host'] ?? 'localhost',
                'port'     => $parse['port'] ?? '',
                'login'    => $parse['user'] ?? '',
                'password' => $parse['pass'] ?? '',
                'driver'   => strtolower($parse['scheme']),
                'database' => isset($parse['path']) ? substr($parse['path'], 1) : '',
                'options'  => $parse['query'] ?? '',
                'prefix'   => $prefix,
                'schema'   => '',
            ];
        }

        $dsn['id'] = "{$dsn['driver']}://{$dsn['login']}:{$dsn['password']}@{$dsn['host']}_{$dsn['prefix']}/{$dsn['database']}/{$dsn['schema']}/{$dsn['options']}";
        return $dsn;
    }
}
