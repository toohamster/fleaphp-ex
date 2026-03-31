<?php

namespace FLEA\Db;

/**
 * SQL 语句封装类
 *
 * 用于封装 SQL 语句字符串或 PDOStatement 对象。
 * 提供统一的接口来判断和处理不同类型的 SQL 资源。
 *
 * 主要功能：
 * - 封装 SQL 语句字符串或 PDOStatement 对象
 * - 判断是否为资源类型（PDOStatement）
 * - 提供工厂方法创建实例
 *
 * 用法示例：
 * ```php
 * // 使用 SQL 字符串创建
 * $stmt = new \FLEA\Db\SqlStatement('SELECT * FROM users');
 *
 * // 使用 PDOStatement 创建
 * $pdoStmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
 * $stmt = new \FLEA\Db\SqlStatement($pdoStmt);
 *
 * // 使用工厂方法
 * $stmt = \FLEA\Db\SqlStatement::create('SELECT * FROM users');
 *
 * // 检查是否为资源类型
 * if ($stmt->isResource()) {
 *     // 是 PDOStatement 对象
 * }
 *
 * // 获取 SQL 内容
 * $sql = $stmt->getSql();
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class SqlStatement
{
    /**
     * @var \PDOStatement|string SQL 语句字符串或 PDOStatement 对象
     */
    private $sql;

    /**
     * @var bool 是否为资源类型（PDOStatement 对象）
     */
    private bool $isResource;

    /**
     * 构造函数
     *
     * @param \PDOStatement|string $sql SQL 语句字符串或 PDOStatement 对象
     */
    public function __construct($sql)
    {
        $this->sql = $sql;
        $this->isResource = is_object($sql);
    }

    /**
     * 判断是否为资源类型（PDOStatement 对象）
     *
     * @return bool 是资源类型返回 true，否则返回 false
     */
    public function isResource(): bool
    {
        return $this->isResource;
    }

    /**
     * 获取 SQL 语句或 PDOStatement 对象
     *
     * @return \PDOStatement|string SQL 语句字符串或 PDOStatement 对象
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * 创建一个新的 SqlStatement 实例
     *
     * 如果传入的已经是 SqlStatement 实例，则直接返回；
     * 否则创建新的实例。
     *
     * 用法示例：
     * ```php
     * // 从字符串创建
     * $stmt = \FLEA\Db\SqlStatement::create('SELECT * FROM users');
     *
     * // 从 PDOStatement 创建
     * $pdoStmt = $pdo->prepare('SELECT * FROM users');
     * $stmt = \FLEA\Db\SqlStatement::create($pdoStmt);
     *
     * // 从 SqlStatement 创建（直接返回原实例）
     * $stmt2 = \FLEA\Db\SqlStatement::create($stmt);
     * ```
     *
     * @param \PDOStatement|self|string $sql SQL 语句字符串、PDOStatement 对象或 SqlStatement 实例
     *
     * @return self 返回新的 SqlStatement 实例或原实例
     */
    public static function create($sql): SqlStatement
    {
        if ($sql instanceof self) {
            return $sql;
        }
        return new self($sql);
    }
}
