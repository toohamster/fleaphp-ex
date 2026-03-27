<?php

namespace FLEA;

/**
 * 数据库连接管理
 *
 * 管理 DSN 解析和数据库连接实例池。
 */
class Database
{
    private static ?self $instance = null;

    /** @var \FLEA\Db\Driver\AbstractDriver[] */
    private array $pool = [];

    private function __construct() {}
    private function __clone() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 获取数据库连接，相同 DSN 复用连接
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
