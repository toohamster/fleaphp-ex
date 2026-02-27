<?php

namespace FLEA\Db\Exception;


/**
 * 定义 \FLEA\Db\Exception\SqlQuery 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: SqlQuery.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * \FLEA\Db\Exception\SqlQuery 异常指示一个 SQL 语句执行错误
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class SqlQuery extends \FLEA\Exception
{
    /**
     * 发生错误的 SQL 语句
     *
     * @var string
     */
    public $sql;

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
