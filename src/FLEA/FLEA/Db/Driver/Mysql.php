<?php

namespace FLEA\Db\Driver;

/**
 * MySQL 数据库驱动程序（基于 PDO）
 *
 * FLEA 框架的 MySQL 数据库驱动实现，使用 PDO 扩展。
 * 提供 MySQL 数据库的连接、查询、事务处理等功能。
 *
 * 主要功能：
 * - PDO 连接管理
 * - SQL 查询执行
 * - 事务支持（包括 Savepoint）
 * - 元数据获取（表结构、字段信息）
 * - 字符串转义
 * - 名称引用（表名、字段名）
 *
 * 用法示例：
 * ```php
 * // 获取数据库驱动实例
 * $dbo = \FLEA::getDBO();
 *
 * // 执行查询
 * $result = $dbo->execute(sql_statement('SELECT * FROM users'));
 * $users = $dbo->getAll($result);
 *
 * // 事务处理
 * $dbo->startTrans();
 * try {
 *     $dbo->execute(sql_statement('INSERT INTO ...'));
 *     $dbo->execute(sql_statement('UPDATE ...'));
 *     $dbo->completeTrans();
 * } catch (\Exception $e) {
 *     $dbo->completeTrans(false);
 * }
 * ```
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 * @see     \FLEA\Db\Driver\AbstractDriver
 */
class Mysql extends \FLEA\Db\Driver\AbstractDriver
{
    /**
     * @var string 序列_next ID SQL 模板
     */
    protected const NEXT_ID_SQL = 'UPDATE %s SET id = LAST_INSERT_ID(id + 1)';

    /**
     * @var string 创建序列 SQL 模板
     */
    protected const CREATE_SEQ_SQL = 'CREATE TABLE %s (id INT NOT NULL)';

    /**
     * @var string 初始化序列 SQL 模板
     */
    protected const INIT_SEQ_SQL = 'INSERT INTO %s VALUES (%s)';

    /**
     * @var string 删除序列 SQL 模板
     */
    protected const DROP_SEQ_SQL = 'DROP TABLE %s';

    /**
     * @var string 获取元数据列 SQL 模板
     */
    protected const META_COLUMNS_SQL = 'SHOW FULL COLUMNS FROM %s';

    /**
     * @var bool MySQL 是否支持插入 ID
     */
    protected const HAS_INSERT_ID = true;

    /**
     * @var bool MySQL 是否支持受影响行数
     */
    protected const HAS_AFFECTED_ROWS = true;

    /**
     * @var bool MySQL 是否支持事务
     */
    protected const HAS_TRANSACTION = true;

    /**
     * @var bool MySQL 是否支持保存点
     */
    protected const HAS_SAVEPOINT = true;

    /**
     * @var \PDO|null PDO 连接句柄
     */
    protected ?\PDO $pdo = null;

    /**
     * @var \PDOStatement|null 最后一次执行的语句
     */
    protected ?\PDOStatement $lastStmt = null;

    /**
     * @var string|null MySQL 版本号
     */
    protected ?string $mysqlVersion = null;

    /**
     * 连接到 MySQL 数据库
     *
     * 使用 PDO 扩展建立数据库连接。
     * 支持自定义端口、字符集、连接选项等配置。
     *
     * @param array|false $dsn 数据库连接配置（false 表示使用已配置的 DSN）
     *
     * @return bool 连接成功返回 true
     *
     * @throws \FLEA\Db\Exception\SqlQuery 连接失败时抛出异常
     */
    public function connect($dsn = false): bool
    {
        $this->lasterr = null;
        $this->lasterrcode = null;

        if ($this->pdo && $dsn == false) {
            return true;
        }
        if (!$dsn) {
            $dsn = $this->dsn;
        } else {
            $this->dsn = $dsn;
        }

        if (isset($dsn['port']) && $dsn['port'] != '') {
            $host = $dsn['host'] . ':' . $dsn['port'];
        } else {
            $host = $dsn['host'];
        }
        if (!isset($dsn['login'])) {
            $dsn['login'] = '';
        }
        if (!isset($dsn['password'])) {
            $dsn['password'] = '';
        }

        try {
            $charset = isset($dsn['charset']) && $dsn['charset'] != '' ? $dsn['charset'] : \FLEA::getAppInf('databaseCharset');

            $dsnString = "mysql:host={$host}";
            if (!empty($dsn['database'])) {
                $dsnString .= ";dbname={$dsn['database']}";
            }
            if (!empty($charset)) {
                $dsnString .= ";charset={$charset}";
            }

            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ];

            if (!empty($dsn['options'])) {
                $options = array_merge($options, $dsn['options']);
            }

            $this->pdo = new \PDO($dsnString, $dsn['login'], $dsn['password'], $options);

            // Set charset explicitly for older MySQL versions
            if (!empty($charset)) {
                $this->pdo->exec("SET NAMES '{$charset}'");
            }

            // Store connection in conn for compatibility
            $this->conn = $this->pdo;

            $this->mysqlVersion = $this->getOne(sql_statement('SELECT VERSION()'));

        } catch (\PDOException $e) {
            $this->lasterr = $e->getMessage();
            $this->lasterrcode = $e->getCode();
            throw new \FLEA\Db\Exception\SqlQuery(
                "PDO Connection failed for host '{$host}'!",
                $this->lasterr,
                $this->lasterrcode
            );
        }

        return true;
    }

    /**
     * 关闭数据库连接
     *
     * @return void
     */
    public function close(): void
    {
        $this->pdo = null;
        $this->conn = null;
        $this->lastStmt = null;
        parent::close();
    }

    /**
     * 选择要操作的数据库
     *
     * @param string $database 数据库名称
     *
     * @return bool 选择成功返回 true
     *
     * @throws \FLEA\Db\Exception\SqlQuery 选择失败时抛出异常
     */
    public function selectDb(string $database): bool
    {
        try {
            $this->pdo->exec("USE `{$database}`");
            return true;
        } catch (\PDOException $e) {
            $this->lasterr = $e->getMessage();
            $this->lasterrcode = $e->getCode();
            throw new \FLEA\Db\Exception\SqlQuery(
                "SELECT DATABASE: '{$database}' FAILED!",
                $this->lasterr,
                $this->lasterrcode
            );
        }
    }

    /**
     * 执行 SQL 查询
     *
     * 执行给定的 SQL 语句，返回 SqlStatement 对象。
     * 支持参数绑定和 SQL 日志记录。
     *
     * @param \FLEA\Db\SqlStatement $sql SQL 语句对象
     * @param array|null $inputarr 参数数组
     * @param bool $throw 查询出错时是否抛出异常
     *
     * @return \FLEA\Db\SqlStatement SQL 语句对象
     * @throws \FLEA\Db\Exception\SqlQuery 查询失败且 $throw=true 时抛出异常
     */
    public function execute(\FLEA\Db\SqlStatement $sql, ?array $inputarr = null, bool $throw = true): \FLEA\Db\SqlStatement
    {
        // 如果已经是 PDOStatement 对象,直接返回
        if ($sql->isResource()) {
            return $sql;
        }

        $sqlStr = $sql->getSql();
        if (is_array($inputarr)) {
            $sqlStr = $this->bind($sqlStr, $inputarr);
        }

        if ($this->enableLog) {
            $this->log[] = $sqlStr;
            log_message("sql: {$sqlStr}", \Psr\Log\LogLevel::DEBUG);
        }

        $this->querycount++;

        try {
            $stmt = $this->pdo->query($sqlStr);
            if ($stmt !== false) {
                $this->lasterr = null;
                $this->lasterrcode = null;
                $this->lastStmt = $stmt;
                return \FLEA\Db\SqlStatement::create($stmt);
            }
        } catch (\PDOException $e) {
            $this->lasterr = $e->getMessage();
            $this->lasterrcode = $e->getCode();

            if ($throw) {
                throw new \FLEA\Db\Exception\SqlQuery($sqlStr, $this->lasterr, $this->lasterrcode);
            }
        }

        return $sql;
    }

    /**
     * 转义字符串以安全用于 SQL 语句
     *
     * 根据数据类型返回合适的 SQL 值表示：
     * - 数字类型：直接返回原值
     * - 布尔类型：返回 1 或 0
     * - NULL：返回 'NULL'
     * - 字符串：使用 PDO quote() 转义
     *
     * @param mixed $value 要转义的值
     *
     * @return mixed 转义后的值
     */
    public function qstr($value)
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        if (is_bool($value)) {
            return $value ? static::TRUE_VALUE : static::FALSE_VALUE;
        }
        if (is_null($value)) {
            return static::NULL_VALUE;
        }
        return $this->pdo->quote($value);
    }

    /**
     * 引用表名
     *
     * 使用反引号 (`) 引用表名，支持 schema 前缀。
     *
     * @param string $tableName 表名
     * @param string|null $schema schema 名称
     *
     * @return string 引用后的表名（如：`table_name` 或 `schema`.`table_name`）
     */
    public function qtable(string $tableName, ?string $schema = null): string
    {
        return $schema != '' ? "`{$schema}`.`{$tableName}`" : "`{$tableName}`";
    }

    /**
     * 引用字段名
     *
     * 使用反引号 (`) 引用字段名，支持表名和 schema 前缀。
     *
     * @param string $fieldName 字段名
     * @param string|null $tableName 表名
     * @param string|null $schema schema 名称
     *
     * @return string 引用后的字段名（如：`field` 或 `table`.`field`）
     */
    public function qfield(string $fieldName, ?string $tableName = null, ?string $schema = null): string
    {
        $fieldName = ($fieldName == '*') ? '*' : "`{$fieldName}`";
        return $tableName != '' ? $this->qtable($tableName, $schema) . '.' . $fieldName : $fieldName;
    }

    /**
     * 获取最后插入 ID
     *
     * @return string|int 最后插入的 ID
     */
    protected function doInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * 获取受影响行数
     *
     * @return int 受影响的行数
     */
    protected function doAffectedRows(): int
    {
        if ($this->lastStmt) {
            return $this->lastStmt->rowCount();
        }
        return 0;
    }

    /**
     *  fetch 一行作为索引数组
     *
     * @param \PDOStatement $res PDO 语句对象
     *
     * @return array|null 索引数组或 null
     */
    public function fetchRow(\PDOStatement $res): ?array
    {
        return $res->fetch(\PDO::FETCH_NUM);
    }

    /**
     * fetch 一行作为关联数组
     *
     * @param \PDOStatement $res PDO 语句对象
     *
     * @return array|null 关联数组或 null
     */
    public function fetchAssoc(\PDOStatement $res): ?array
    {
        return $res->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * 释放结果集
     *
     * @param \PDOStatement $res PDO 语句对象
     *
     * @return bool 始终返回 true（PDO 自动释放）
     */
    public function freeRes(\PDOStatement $res): bool
    {
        return true; // PDO statements are automatically freed
    }

    /**
     * 带限制的 SELECT 查询
     *
     * @param string $sql SQL 语句
     * @param int|null $length 返回记录数上限
     * @param int|null $offset 起始偏移量
     *
     * @return \FLEA\Db\SqlStatement SqlStatement 对象
     * @throws \FLEA\Db\Exception\SqlQuery 查询失败时抛出异常
     */
    public function selectLimit(string $sql, ?int $length = null, ?int $offset = null): \FLEA\Db\SqlStatement
    {
        if (!is_null($offset)) {
            $sql .= " LIMIT " . (int)$offset;
            if (!is_null($length)) {
                $sql .= ', ' . (int)$length;
            } else {
                $sql .= ', 4294967294';
            }
        } elseif (!is_null($length)) {
            $sql .= " LIMIT " . (int)$length;
        }
        return $this->execute(\FLEA\Db\SqlStatement::create($sql));
    }

    /**
     * 获取表的列元数据
     *
     * 使用 SHOW FULL COLUMNS 命令获取表的字段定义信息。
     * 返回的元数据包含字段名、类型、长度、是否为主键、是否自增等信息。
     *
     * 字段简单类型映射：
     * - C: CHAR 或 VARCHAR 类型字段
     * - X: TEXT 或 CLOB 类型字段
     * - B: 二进制数据（BLOB）
     * - N: 数值或者浮点数
     * - D: 日期
     * - T: TimeStamp
     * - L: 逻辑布尔值
     * - I: 整数
     * - R: 自动增量或计数器
     *
     * @param string $table 表名
     *
     * @return array|false 元数据数组或 false（失败时）
     *
     * @throws \FLEA\Db\Exception\SqlQuery 查询失败时抛出异常
     */
    public function metaColumns(string $table)
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
        static $typeMap = [
            'BIT' => 'I',
            'TINYINT' => 'I',
            'BOOL' => 'L',
            'BOOLEAN' => 'L',
            'SMALLINT' => 'I',
            'MEDIUMINT' => 'I',
            'INT' => 'I',
            'INTEGER' => 'I',
            'BIGINT' => 'I',
            'FLOAT' => 'N',
            'DOUBLE' => 'N',
            'DOUBLEPRECISION' => 'N',
            'DECIMAL' => 'N',
            'DEC' => 'N',

            'DATE' => 'D',
            'DATETIME' => 'T',
            'TIMESTAMP' => 'T',
            'TIME' => 'T',
            'YEAR' => 'I',

            'CHAR' => 'C',
            'NCHAR' => 'C',
            'VARCHAR' => 'C',
            'NVARCHAR' => 'C',
            'BINARY' => 'B',
            'VARBINARY' => 'B',
            'TINYBLOB' => 'X',
            'TINYTEXT' => 'X',
            'BLOB' => 'X',
            'TEXT' => 'X',
            'MEDIUMBLOB' => 'X',
            'MEDIUMTEXT' => 'X',
            'LONGBLOB' => 'X',
            'LONGTEXT' => 'X',
            'ENUM' => 'C',
            'SET' => 'C',
        ];

        $rs = $this->execute(\FLEA\Db\SqlStatement::create(sprintf(static::META_COLUMNS_SQL, $table)));
        $rs = $rs->getSql();
        if (!$rs) {
            return false;
        }
        $retarr = [];
        while (($row = $this->fetchAssoc($rs))) {
            $field = [];
            $field['name'] = $row['Field'];
            $type = $row['Type'];

            $field['scale'] = null;
            $queryArray = false;
            if (preg_match('/^(.+)\((\d+),(\d+)/', $type, $queryArray)) {
                $field['type'] = $queryArray[1];
                $field['maxLength'] = is_numeric($queryArray[2]) ? $queryArray[2] : -1;
                $field['scale'] = is_numeric($queryArray[3]) ? $queryArray[3] : -1;
            } elseif (preg_match('/^(.+)\((\d+)/', $type, $queryArray)) {
                $field['type'] = $queryArray[1];
                $field['maxLength'] = is_numeric($queryArray[2]) ? $queryArray[2] : -1;
            } elseif (preg_match('/^(enum)\((.*)\)$/i', $type, $queryArray)) {
                $field['type'] = $queryArray[1];
                $arr = explode(",", $queryArray[2]);
                $field['enums'] = $arr;
                $zlen = max(array_map("strlen", $arr)) - 2;
                $field['maxLength'] = ($zlen > 0) ? $zlen : 1;
            } else {
                $field['type'] = $type;
                $field['maxLength'] = -1;
            }
            $field['simpleType'] = $typeMap[strtoupper($field['type'])];

            $field['notNull'] = ($row['Null'] != 'YES');
            $field['primaryKey'] = ($row['Key'] == 'PRI');
            $field['autoIncrement'] = (strpos($row['Extra'], 'auto_increment') !== false);
            if ($field['autoIncrement']) {
                $field['simpleType'] = 'R';
            }
            $field['binary'] = (strpos($type, 'blob') !== false);
            $field['unsigned'] = (strpos($type, 'unsigned') !== false);

            if ($field['type'] == 'tinyint' && $field['maxLength'] == 1) {
                $field['simpleType'] = 'L';
            }

            if (!$field['binary']) {
                $d = $row['Default'];
                if ($d != '' && $d != 'NULL') {
                    $field['hasDefault'] = true;
                    $field['defaultValue'] = $this->setValueByType($d, $field['simpleType']);
                } else {
                    $field['hasDefault'] = false;
                }
            }

            $field['description'] = $row['Comment'] ?? '';

            $retarr[strtoupper($field['name'])] = $field;
        }
        $this->freeRes($rs);
        return $retarr;
    }

    /**
     * 获取表列表
     *
     * 使用 SHOW TABLES 命令获取数据库中的表名列表。
     * 支持按模式匹配和指定 schema 过滤。
     *
     * @param string|null $pattern 表名模式（可选，使用 LIKE 匹配）
     * @param string|null $schema schema 名称（可选）
     *
     * @return array 表名数组
     */
    public function metaTables(?string $pattern = null, ?string $schema = null): array
    {
        $sql = 'SHOW TABLES';
        if (!empty($schema)) {
            $sql .= " FROM {$schema}";
        }
        if (!empty($pattern)) {
            $sql .= ' LIKE ' . $this->qstr($pattern);
        }
        $res = $this->execute(\FLEA\Db\SqlStatement::create($sql), null, false);
        $res = $res->getSql();
        $tables = [];
        while (($row = $this->fetchRow($res))) {
            $tables[] = reset($row);
        }
        $this->freeRes($res);
        return $tables;
    }

    /**
     * 启动 MySQL 事务
     *
     * @return void
     */
    protected function doStartTrans(): void
    {
        $this->pdo->beginTransaction();
    }

    /**
     * 完成 MySQL 事务
     *
     * 根据 $commitOnNoErrors 参数和是否有失败查询来决定提交还是回滚事务。
     *
     * @param bool $commitOnNoErrors 无错误时是否提交事务（true 提交，false 回滚）
     *
     * @return void
     */
    protected function doCompleteTrans(bool $commitOnNoErrors): void
    {
        if ($commitOnNoErrors && !$this->hasFailedQuery) {
            $this->pdo->commit();
        } else {
            $this->pdo->rollBack();
        }
    }
}

