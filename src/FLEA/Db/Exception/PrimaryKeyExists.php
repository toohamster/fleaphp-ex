<?php

namespace FLEA\Db\Exception;

/**
 * PrimaryKeyExists 异常
 *
 * 指示在不需要主键值的时候却提供了主键值。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class PrimaryKeyExists extends \FLEA\Exception
{
    /**
     * @var string 主键字段名
     */
    public string $primaryKey;

    /**
     * @var mixed 主键字段值
     */
    public $pkValue;

    /**
     * 构造函数
     *
     * @param string $pk      主键字段名
     * @param mixed  $pkValue 主键字段值
     */
    public function __construct($pk, $pkValue = null)
    {
        $this->primaryKey = $pk;
        $this->pkValue = $pkValue;
        $code = 0x06ff004;
        $msg = sprintf(_ET($code), $pk, $pkValue);
        parent::__construct($msg, $code);
    }
}
