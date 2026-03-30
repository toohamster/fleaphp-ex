<?php

namespace FLEA\Db\Exception;

/**
 * SqlQuery 异常
 *
 * 指示一个 SQL 语句执行错误。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class SqlQuery extends \FLEA\Exception
{
    /**
     * @var string 发生错误的 SQL 语句
     */
    public string $sql;

    /**
     * 构造函数
     *
     * @param string $sql SQL 语句
     * @param string $msg 错误消息
     * @param int    $code 错误代码
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
