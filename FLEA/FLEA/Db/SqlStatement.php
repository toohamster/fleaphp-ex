<?php

namespace FLEA\Db;

/**
 * SQL语句封装类
 *
 * 该类用于封装SQL语句或PDOStatement对象，提供统一的接口来处理数据库查询。
 * 支持判断当前封装的是字符串形式的SQL还是PDOStatement资源。
 */
class SqlStatement
{
    /**
     * @var \PDOStatement|string
     */
    private $sql;

    /**
     * 是否引用
     *
     * @var bool
     */
    private bool $isResource;

    /**
     * 构造函数
     *
     * @param \PDOStatement|string $sql SQL语句或PDOStatement对象
     */
    public function __construct($sql)
    {
        $this->sql = $sql;
        $this->isResource = is_object($sql);
    }

    /**
     * @return bool
     */
    public function isResource(): bool
    {
        return $this->isResource;
    }

    /**
     * 获取SQL语句或PDOStatement对象
     *
     * @return \PDOStatement|string
     */
    public function getSql()
    {
        return $this->sql;
    }

    /**
     * 创建一个新的SqlStatement实例
     *
     * @param \PDOStatement|self|string $sql SQL语句字符串
     * @return self 返回新的SqlStatement实例
     */
    public static function create($sql): SqlStatement
    {
        if ($sql instanceof self) {
            return $sql;
        }
        return new self($sql);
    }
}