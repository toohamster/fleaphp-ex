<?php

namespace FLEA\Db\Driver;


/**
 * 定义 \FLEA\Db\Driver\Mysql 驱动
 *
 * @author toohamster
 * @package Core
 * @version $Id: Mysql.php 1077 2008-05-14 17:44:19Z dualface $
 * Updated: Converted to PDO for PHP 7.4 compatibility
 */


/**
 * 用于 PDO MySQL 的数据库驱动程序
 *
 * @package Core
 * @author toohamster
 * @version 2.0
 */
class Mysql extends \FLEA\Db\Driver\AbstractDriver
{
    /**
     * @var string
     */
    public $NEXT_ID_SQL = 'UPDATE %s SET id = LAST_INSERT_ID(id + 1)';
    public $CREATE_SEQ_SQL = 'CREATE TABLE %s (id INT NOT NULL)';
    public $INIT_SEQ_SQL = 'INSERT INTO %s VALUES (%s)';
    public $DROP_SEQ_SQL = 'DROP TABLE %s';
    public $META_COLUMNS_SQL = 'SHOW FULL COLUMNS FROM %s';
    public $PARAM_STYLE = DBO_PARAM_QM;
    public $HAS_INSERT_ID = true;
    public $HAS_AFFECTED_ROWS = true;
    /**
     * @var \PDO
     */
    protected $pdo = null;
    /**
     * @var \PDOStatement
     */
    protected $lastStmt = null;
    /**
     * @var string
     */
    protected $_mysqlVersion = null;

    /**
     * Connect to database using PDO
     *
     * @param array|false $dsn
     * @return bool
     * @throws \FLEA\Db\Exception\SqlQuery
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

            $this->_mysqlVersion = $this->getOne(sql_statement('SELECT VERSION()'));

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
     * Close database connection
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
     * Select database
     *
     * @param string $database
     * @return bool
     * @throws \FLEA\Db\Exception\SqlQuery
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
     * Execute SQL query
     *
     * @param \FLEA\Db\SqlStatement $sql
     * @param array|null $inputarr
     * @param bool $throw
     * @return \FLEA\Db\SqlStatement
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
     * Quote string for safe SQL usage
     *
     * @param mixed $value
     * @return mixed
     */
    public function qstr($value)
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }
        if (is_bool($value)) {
            return $value ? $this->TRUE_VALUE : $this->FALSE_VALUE;
        }
        if (is_null($value)) {
            return $this->NULL_VALUE;
        }
        return $this->pdo->quote($value);
    }

    /**
     * Quote table name
     *
     * @param string $tableName
     * @param string|null $schema
     * @return string
     */
    public function qtable(string $tableName, ?string $schema = null): string
    {
        return $schema != '' ? "`{$schema}`.`{$tableName}`" : "`{$tableName}`";
    }

    /**
     * Quote field name
     *
     * @param string $fieldName
     * @param string|null $tableName
     * @param string|null $schema
     * @return string
     */
    public function qfield(string $fieldName, ?string $tableName = null, ?string $schema = null): string
    {
        $fieldName = ($fieldName == '*') ? '*' : "`{$fieldName}`";
        return $tableName != '' ? $this->qtable($tableName, $schema) . '.' . $fieldName : $fieldName;
    }

    /**
     * Get last insert ID
     *
     * @return string|int
     */
    protected function insertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Get affected rows count
     *
     * @return int
     */
    protected function affectedRows(): int
    {
        if ($this->lastStmt) {
            return $this->lastStmt->rowCount();
        }
        return 0;
    }

    /**
     * Fetch row as indexed array
     *
     * @param \PDOStatement $res
     * @return array|null
     */
    public function fetchRow(\PDOStatement $res): ?array
    {
        return $res->fetch(\PDO::FETCH_NUM);
    }

    /**
     * Fetch row as associative array
     *
     * @param \PDOStatement $res
     * @return array|null
     */
    public function fetchAssoc(\PDOStatement $res): ?array
    {
        return $res->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Free result
     *
     * @param \PDOStatement $res
     * @return bool
     */
    public function freeRes(\PDOStatement $res): bool
    {
        return true; // PDO statements are automatically freed
    }

    /**
     * Select with limit
     *
     * @param string $sql
     * @param int|null $length
     * @param int|null $offset
     * @return \FLEA\Db\SqlStatement
     * @throws \FLEA\Db\Exception\SqlQuery
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
     * Get column metadata
     *
     * @param string $table
     * @return array|false
     * @throws \FLEA\Db\Exception\SqlQuery
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

        $rs = $this->execute(\FLEA\Db\SqlStatement::create(sprintf($this->META_COLUMNS_SQL, $table)));
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
     * Get list of tables
     *
     * @param string|null $pattern
     * @param string|null $schema
     * @return array
     * @@throws Exception
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
}


