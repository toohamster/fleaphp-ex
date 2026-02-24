<?php

namespace FLEA\Db\Driver;

/**
 * 定义 \FLEA\Db\Driver\AbstractDriver 类
 *
 * @author toohamster
 * @package Core
 * @version $Id: Abstract.php 1025 2008-01-09 04:17:59Z qeeyuan $
 */

// {{{ constants
/**
 * 问号作为参数占位符
 */
define('DBO_PARAM_QM',          '?');
/**
 * 冒号开始的命名参数
 */
define('DBO_PARAM_CL_NAMED',    ':');
/**
 * $符号开始的序列
 */
define('DBO_PARAM_DL_SEQUENCE', '$');
/**
 * @开始的命名参数
 */
define('DBO_PARAM_AT_NAMED',    '@');

/**
 * \FLEA\Db\Driver\AbstractDriver 是所有数据库驱动的抽象基础类
 *
 * @package Core
 * @author toohamster
 * @version 1.1
 */
abstract class AbstractDriver
{
    /**
     * 用于描绘 true、false 和 null 的数据库值
     */
    public $TRUE_VALUE  = 1;
    public $FALSE_VALUE = 0;
    public $NULL_VALUE = 'NULL';

    /**
     * 用于 genSeq()、dropSeq() 和 nextId() 的 SQL 查询语句
     */
    public $NEXT_ID_SQL    = null;
    public $CREATE_SEQ_SQL = null;
    public $INIT_SEQ_SQL   = null;
    public $DROP_SEQ_SQL   = null;

    /**
     * 用于获取元数据的 SQL 查询语句
     */
    public $META_COLUMNS_SQL = null;

    /**
     * 指示使用何种样式的参数占位符
     *
     * @var int
     */
    public $PARAM_STYLE = DBO_PARAM_QM;

    /**
     * 指示数据库是否有自增字段功能
     *
     * @var boolean
     */
    public $HAS_INSERT_ID  = false;

    /**
     * 指示数据库是否能获得更新、删除操作影响的记录行数量
     *
     * @var boolean
     */
    public $HAS_AFFECTED_ROWS = false;

    /**
     * 指示数据库是否支持事务
     *
     * @var boolean
     */
    public $HAS_TRANSACTION = false;

    /**
     * 指示数据库是否支持事务中的 SAVEPOINT 功能
     *
     * @var boolean
     */
    public $HAS_SAVEPOINT = false;

    /**
     * 指示是否将查询结果中的字段名转换为全小写
     *
     * @var boolean
     */
    public $RESULT_FIELD_NAME_LOWER = false;

    /**
     * 数据库连接信息
     *
     * @var array
     */
    public $dsn = null;

    /**
     * 数据库连接句柄
     *
     * @var resource
     */
    public $conn = null;

    /**
     * 所有 SQL 查询的日志
     *
     * @var array
     */
    public $log = [];

    /**
     * 执行的查询计数
     *
     * @var int
     */
    public $querycount = 0;

    /**
     * 最后一次数据库操作的错误信息
     *
     * @var mixed
     */
    public $lasterr = null;

    /**
     * 最后一次数据库操作的错误代码
     *
     * @var mixed
     */
    public $lasterrcode = null;

    /**
     * 最近一次插入操作或者 nextId() 操作返回的插入 ID
     *
     * @var mixed
     */
    protected $_insertId = null;

    /**
     * 指示事务启动次数
     *
     * @var int
     */
    protected $_transCount = 0;

    /**
     * 指示事务执行期间是否发生了错误
     *
     * @var boolean
     */
    protected $_hasFailedQuery = false;

    /**
     * SAVEPOINT 堆栈
     *
     * @var array
     */
    protected $_savepointStack = [];

    /**
     * 构造函数
     *
     * @param array $dsn
     */
    public function __construct(?array $dsn = null)
    {
        $tmp = (array)$dsn;
        unset($tmp['password']);
        $this->dsn = $dsn;
        $this->enableLog = FLEA::getAppInf('logEnabled');
        if (!function_exists('log_message')) {
            $this->enableLog = false;
        }
    }

    /**
     * 连接数据库
     *
     * @param array $dsn
     *
     * @return boolean
     */
    abstract public function connect($dsn = false): bool;

    /**
     * 关闭数据库连接
     */
    public function close(): void
    {
        $this->conn = null;
        $this->lasterr = null;
        $this->lasterrcode = null;
        $this->_insertId = null;
        $this->_transCount = 0;
        $this->_transCommit = true;
    }

    /**
     * 选择要操作的数据库
     *
     * @param string $database
     *
     * @return boolean
     */
    abstract public function selectDb(string $database): bool;

    /**
     * 执行一个查询，返回一个 resource 或者 boolean 值
     *
     * @param string $sql
     * @param array $inputarr
     * @param boolean $throw 指示查询出错时是否抛出异常
     *
     * @return resource|boolean
     */
    abstract public function execute(string $sql, ?array $inputarr = null, bool $throw = true);

    /**
     * 转义字符串
     *
     * @param string $value
     *
     * @return mixed
     */
    abstract public function qstr($value): string;

    /**
     * 按照指定的类型，返回值
     *
     * @param mixed $value
     * @param string $type
     *
     * @return mixed
     */
    public function setValueByType($value, string $type): string
    {
        /**
         *  C CHAR 或 VARCHAR 类型字段
         *  X TEXT 或 CLOB 类型字段
         *  B 二进制数据（BLOB）
         *  N 数值或者浮点数
         *  D 日期
         *  T TimeStamp
         *  L 逻辑布尔值
         *  I 整数
         *  R 自动增量或计数器
         */
        switch (strtoupper($type)) {
        case 'I':
            return (int)$value;
        case 'N':
            return (float)$value;
        case 'L':
            return (bool)$value;
        default:
            return $value;
        }
    }

    /**
     * 将数据表名字转换为完全限定名
     *
     * @param string $tableName
     * @param string $schema
     *
     * @return string
     */
    abstract public function qtable(string $tableName, ?string $schema = null): string;

    /**
     * 将字段名转换为完全限定名，避免因为字段名和数据库关键词相同导致的错误
     *
     * @param string $fieldName
     * @param string $tableName
     * @param string $schema
     *
     * @return string
     */
    abstract public function qfield(string $fieldName, ?string $tableName = null, ?string $schema = null): string;

    /**
     * 一次性将多个字段名转换为完全限定名
     *
     * @param string|array $fields
     * @param string $tableName
     * @param string $schema
     * @param boolean $returnArray
     *
     * @return string
     */
    public function qfields($fields, ?string $tableName = null, ?string $schema = null, bool $returnArray = false)
    {
        if (!is_array($fields)) {
            $fields = explode(',', $fields);
            $fields = array_map('trim', $fields);
        }
        $return = [];
        foreach ($fields as $fieldName) {
            $return[] = $this->qfield($fieldName, $tableName, $schema);
        }
        return $returnArray ? $return : implode(', ', $return);
    }

    /**
     * 为数据表产生下一个序列值
     *
     * @param string $seqName
     * @param string $startValue
     *
     * @return int
     */
    public function nextId(string $seqName = 'sdbo_seq', int $startValue = 1)
    {
        $getNextIdSql = sprintf($this->NEXT_ID_SQL, $seqName);
        $result = $this->execute($getNextIdSql, null, false);
        if (!$result) {
            if (!$this->createSeq($seqName, $startValue)) { return false; }
            $result = $this->execute($getNextIdSql);
            if (!$result) { return false; }
        }

        if ($this->HAS_INSERT_ID) {
            return $this->_insertId();
        } else {
            $row = $this->fetchRow($result);
            $this->freeRes($result);
            $nextId = reset($row);
            $this->_insertId = $nextId;
            return $nextId;
        }
    }

    /**
     * 创建一个新的序列，成功返回 true，失败返回 false
     *
     * @param string $seqName
     * @param int $startValue
     *
     * @return boolean
     */
    public function createSeq(string $seqName = 'sdbo_seq', int $startValue = 1): bool
    {
        if ($this->execute(sprintf($this->CREATE_SEQ_SQL, $seqName))) {
            return $this->execute(sprintf($this->INIT_SEQ_SQL, $seqName, $startValue - 1));
        } else {
            return false;
        }
    }

    /**
     * 删除一个序列
     *
     * 具体的实现与数据库系统有关。
     *
     * @param string $seqName
     */
    public function dropSeq(string $seqName = 'sdbo_seq'): bool
    {
        return $this->execute(sprintf($this->DROP_SEQ_SQL, $seqName));
    }

    /**
     * 获取最后一次 nextId 操作获得的值
     *
     * @return mixed
     */
    public function insertId()
    {
        return $this->HAS_INSERT_ID ? $this->_insertId() : $this->_insertId;
    }

    /**
     * 返回最近一次数据库操作受到影响的记录数
     *
     * @return int
     */
    public function affectedRows(): int
    {
        return $this->HAS_AFFECTED_ROWS ? $this->_affectedRows() : false;
    }

    /**
     * 从记录集中返回一行数据
     *
     * @param PDOStatement $res
     *
     * @return array
     */
    abstract public function fetchRow(PDOStatement $res): ?array;

    /**
     * 从记录集中返回一行数据，字段名作为键名
     *
     * @param PDOStatement $res
     *
     * @return array
     */
    abstract public function fetchAssoc(PDOStatement $res): ?array;

    /**
     * 释放查询句柄
     *
     * @param PDOStatement $res
     *
     * @return boolean
     */
    abstract public function freeRes(PDOStatement $res): bool;

    /**
     * 进行限定记录集的查询
     *
     * @param string $sql
     * @param int $length
     * @param int $offset
     *
     * @return resource
     */
    abstract public function selectLimit(string $sql, ?int $length = null, ?int $offset = null);

    /**
     * 执行一个查询，返回查询结果记录集、指定字段的值集合以及以该字段值分组后的记录集
     *
     * @param string|resource $sql
     * @param string $field
     * @param array $fieldValues
     * @param array $reference
     *
     * @return array
     */
    public function getAllWithFieldRefs($sql, string $field, array &$fieldValues, array &$reference): ?array
    {
        $res = is_resource($sql) ? $sql : $this->execute($sql);
        $fieldValues = [];
        $reference = [];
        $offset = 0;
        $data = [];

        while ($row = $this->fetchAssoc($res)) {
            $fieldValue = $row[$field];
            unset($row[$field]);
            $data[$offset] = $row;
            $fieldValues[$offset] = $fieldValue;
            $reference[$fieldValue] =& $data[$offset];
            $offset++;
        }
        $this->freeRes($res);
        return $data;
    }

    /**
     * 执行一个查询，并将数据按照指定字段分组后与 $assocRowset 记录集组装在一起
     *
     * @param string|resource $sql
     * @param array $assocRowset
     * @param string $mappingName
     * @param boolean $oneToOne
     * @param string $refKeyName
     * @param mixed $limit
     */
    public function assemble(string $sql, array &$assocRowset, string $mappingName, bool $oneToOne, string $refKeyName, $limit = null): void
    {
        if (is_resource($sql)) {
            $res = $sql;
        } else {
            if (!is_null($limit)) {
                if (is_array($limit)) {
                    list($length, $offset) = $limit;
                } else {
                    $length = $limit;
                    $offset = 0;
                }
                $res = $this->selectLimit($sql, $length, $offset);
            } else {
                $res = $this->execute($sql);
            }
        }

        if ($oneToOne) {
            // 一对一组装数据
            while ($row = $this->fetchAssoc($res)) {
                $rkv = $row[$refKeyName];
                unset($row[$refKeyName]);
                $assocRowset[$rkv][$mappingName] = $row;
            }
        } else {
            // 一对多组装数据
            while ($row = $this->fetchAssoc($res)) {
                $rkv = $row[$refKeyName];
                unset($row[$refKeyName]);
                $assocRowset[$rkv][$mappingName][] = $row;
            }
        }
        $this->freeRes($res);
    }

    /**
     * 执行一个查询，返回查询结果记录集
     *
     * @param string|resource $sql
     *
     * @return array
     */
    public function getAll($sql): ?array
    {
        $res = is_resource($sql) ? $sql : $this->execute($sql);
        $rowset = [];
        while ($row = $this->fetchAssoc($res)) {
            $rowset[] = $row;
        }
        $this->freeRes($res);
        return $rowset;
    }

    /**
     * 执行查询，返回第一条记录的第一个字段
     *
     * @param string|resource $sql
     *
     * @return mixed
     */
    public function getOne(string $sql)
    {
        $res = is_resource($sql) ? $sql : $this->execute($sql);
        $row = $this->fetchRow($res);
        $this->freeRes($res);
        return isset($row[0]) ? $row[0] : null;
    }

    /**
     * 执行查询，返回第一条记录
     *
     * @param string|resource $sql
     *
     * @return mixed
     */
    public function getRow(string $sql): ?array
    {
        $res = is_resource($sql) ? $sql : $this->execute($sql);
        $row = $this->fetchAssoc($res);
        $this->freeRes($res);
        return $row;
    }

    /**
     * 执行查询，返回结果集的指定列
     *
     * @param string|resource $sql
     * @param int $col 要返回的列，0 为第一列
     *
     * @return mixed
     */
    public function getCol(string $sql, int $col = 0): array
    {
        $res = is_resource($sql) ? $sql : $this->execute($sql);
        $data = [];
        while ($row = $this->fetchRow($res)) {
            $data[] = $row[$col];
        }
        $this->freeRes($res);
        return $data;
    }

    /**
     * 执行一个查询，返回分组后的查询结果记录集
     *
     * $groupBy 参数如果为字符串或整数，表示结果集根据 $groupBy 参数指定的字段进行分组。
     * 如果 $groupBy 参数为 true，则表示根据每行记录的第一个字段进行分组。
     *
     * @param string|resource $sql
     * @param string|int|boolean $groupBy
     *
     * @return array
     */
    public function getAllGroupBy(string $sql, array &$groupBy): array
    {
        if (is_resource($sql)) {
            $res = $sql;
        } else {
            $res = $this->execute($sql);
        }
        $data = [];
        $row = $this->fetchAssoc($res);
        if ($row != false) {
            if ($groupBy === true) {
                $groupBy = key($row);
            }
            do {
                $rkv = $row[$groupBy];
                unset($row[$groupBy]);
                $data[$rkv][] = $row;
            } while ($row = $this->fetchAssoc($res));
        }
        $this->freeRes($res);
        return $data;
    }

    /**
     * 返回指定表（或者视图）的元数据
     *
     * 部分代码参考 ADOdb 实现。
     *
     * 每个字段包含下列属性：
     *
     * name:            字段名
     * scale:           小数位数
     * type:            字段类型
     * simpleType:      简单字段类型（与数据库无关）
     * maxLength:       最大长度
     * notNull:         是否不允许保存 NULL 值
     * primaryKey:      是否是主键
     * autoIncrement:   是否是自动增量字段
     * binary:          是否是二进制数据
     * unsigned:        是否是无符号数值
     * hasDefault:      是否有默认值
     * defaultValue:    默认值
     *
     * @param string $table
     *
     * @return array
     */
    abstract public function metaColumns(string $table);

    /**
     * 获得所有数据表的名称
     *
     * @param string $pattern
     * @param string $schema
     *
     * @return array
     */
    abstract public function metaTables(?string $pattern = null, ?string $schema = null): array;

    /**
     * 返回数据库可以接受的日期格式
     *
     * @param int $timestamp
     */
    public function dbTimeStamp(?int $timestamp = null): string
    {
        return date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * 启动事务
     */
    public function startTrans(): bool
    {
        if (!$this->HAS_TRANSACTION) { return false; }
        if ($this->_transCount == 0) {
            $this->_startTrans();
            $this->_hasFailedQuery = false;
        }
        $this->_transCount++;
        if ($this->_transCount > 1 && $this->HAS_SAVEPOINT) {
            $savepoint = 'savepoint_' . $this->_transCount;
            $this->execute("SAVEPOINT {$savepoint}");
            array_push($this->_savepointStack, $savepoint);
        }
    }

    /**
     * 完成事务，根据查询是否出错决定是提交事务还是回滚事务
     *
     * 如果 $commitOnNoErrors 参数为 true，当事务中所有查询都成功完成时，则提交事务，否则回滚事务
     * 如果 $commitOnNoErrors 参数为 false，则强制回滚事务
     *
     * @param bool $commitOnNoErrors 指示在没有错误时是否提交事务
     */
    public function completeTrans(bool $commitOnNoErrors = true): bool
    {
        if (!$this->HAS_TRANSACTION) { return false; }
        if ($this->_transCount == 0) { return true; }
        $this->_transCount--;
        if ($this->_transCount > 0 && $this->HAS_SAVEPOINT) {
            $savepoint = array_pop($this->_savepointStack);
            if ($this->_hasFailedQuery || $commitOnNoErrors == false) {
                $this->execute("ROLLBACK TO SAVEPOINT {$savepoint}");
            }
        } else {
            $this->_completeTrans($commitOnNoErrors);
        }
    }

    /**
     * 强制指示在调用 completeTrans() 时回滚事务
     */
    public function failTrans(): void
    {
        $this->_hasFailedQuery = true;
    }

    /**
     * 返回事务是否失败的状态
     */
    public function hasFailedTrans(): bool
    {
        return $this->HAS_TRANSACTION ? $this->_hasFailedQuery : false;
    }

    /**
     * 根据 SQL 语句和提供的参数数组，生成最终的 SQL 语句
     *
     * @param string $sql
     * @param array $inputarr
     *
     * @return string
     */
    public function bind(string $sql, array &$inputarr): string
    {
        $arr = explode('?', $sql);
        $sql = array_shift($arr);
        foreach ($inputarr as $value) {
            if (isset($arr[0])) {
                $sql .= $this->qstr($value) . array_shift($arr);
            }
        }
        return $sql;
    }

    /**
     * 根据包含记录内容的数组返回一条有效的 SQL 插入记录语句
     *
     * @param array $row
     * @param string $table 要插入的数据表
     * @param string $schema
     *
     * @return string
     */
    public function getInsertSQL(array &$row, string $table, ?string $schema = null): string
    {
        list($holders, $values) = $this->getPlaceholder($row);
        $holders = implode(',', $holders);
        $fields = $this->qfields(array_keys($values));
        $table = $this->qtable($table, $schema);
        $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$holders})";
        return $sql;
    }

    public function getUpdateSQL(array &$row, $pk, string $table, ?string $schema = null): string
    {
        $pkv = $row[$pk];
        unset($row[$pk]);
        [$pairs, ] = $this->getPlaceholderPair($row);
        $row[$pk] = $pkv;
        $pairs = implode(',', $pairs);
        $table = $this->qtable($table, $schema);
        $pk = $this->qfield($pk);
        $sql = "UPDATE {$table} SET {$pairs} WHERE {$pk} = " . $this->qstr($pkv);
        return $sql;
    }

    /**
     * 根据驱动的参数占位符样式，返回包含参数占位符及有效数据的数组
     *
     * @param array $inputarr
     * @param array|null $fields
     *
     * @return array
     */
    public function getPlaceholder(array &$inputarr, $fields = null): string
    {
        $holders = [];
        $values = [];
        if (is_array($fields)) {
            $fields = array_change_key_case(array_flip($fields), CASE_LOWER);
            foreach (array_keys($inputarr) as $key) {
                if (!isset($fields[strtolower($key)])) { continue; }
                if ($this->PARAM_STYLE == DBO_PARAM_QM) {
                    $holders[] = $this->PARAM_STYLE;
                } else {
                    $holders[] = $this->PARAM_STYLE . $key;
                }
                $values[$key] =& $inputarr[$key];
            }
        } else {
            foreach (array_keys($inputarr) as $key) {
                if ($this->PARAM_STYLE == DBO_PARAM_QM) {
                    $holders[] = $this->PARAM_STYLE;
                } else {
                    $holders[] = $this->PARAM_STYLE . $key;
                }
                $values[$key] =& $inputarr[$key];
            }
        }
        return array($holders, $values);
    }

    /**
     * 根据驱动的参数占位符样式，返回包含参数及占位符字符串对、有效数据的数组
     *
     * @param array $inputarr
     * @param array $fields
     *
     * @return array
     */
    public function getPlaceholderPair(array &$inputarr, $fields = null): string
    {
        $pairs = [];
        $values = [];
        if (is_array($fields)) {
            $fields = array_change_key_case(array_flip($fields), CASE_LOWER);
            foreach (array_keys($inputarr) as $key) {
                if (!isset($fields[strtolower($key)])) { continue; }
                $qkey = $this->qfield($key);
                if ($this->PARAM_STYLE == DBO_PARAM_QM) {
                    $pairs[] = "{$qkey}={$this->PARAM_STYLE}";
                } else {
                    $pairs[] = "{$qkey}={$this->PARAM_STYLE}{$key}";
                }
                $values[$key] =& $inputarr[$key];
            }
        } else {
            foreach (array_keys($inputarr) as $key) {
                $qkey = $this->qfield($key);
                if ($this->PARAM_STYLE == DBO_PARAM_QM) {
                    $pairs[] = "{$qkey}={$this->PARAM_STYLE}";
                } else {
                    $pairs[] = "{$qkey}={$this->PARAM_STYLE}{$key}";
                }
                $values[$key] =& $inputarr[$key];
            }
        }
        return array($pairs, $values);
    }
}
