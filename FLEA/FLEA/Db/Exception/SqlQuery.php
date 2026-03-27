<?php

namespace FLEA\Db\Exception;

/**
 * \FLEA\Db\Exception\SqlQuery 异常指示一个 SQL 语句执行错误
 *
 */
class SqlQuery extends \FLEA\Exception
{
    /**
     * 发生错误的 SQL 语句
     *
     * @var string
     */
    public string $sql;

    /**
     * 构造函数
     *
     * @param string $sql
     * @param string $msg
     * @param int $code
     */
    public function __construct($sql, $msg = 0, $code = 0)
    {
        $this->sql = $sql;
        if ($msg) {
            $code = 0x06ff005;
            $msg = sprintf(_ET($code), $msg, $sql, $code);
        } else {
            $code = 0x06ff006;
            $msg = sprintf(_ET($code), $sql, $code);
        }
        parent::__construct($msg, $code);
    }
}
