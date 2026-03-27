<?php

namespace FLEA\Db\Exception;

/**
 * \FLEA\Db\Exception\PrimaryKeyExists 异常指示在不需要主键值的时候却提供了主键值
 *
 */
class PrimaryKeyExists extends \FLEA\Exception
{
    /**
     * 主键字段名
     *
     * @var string
     */
    public string $primaryKey;

    /**
     * 主键字段值
     *
     * @var mixed
     */
    public $pkValue;

    /**
     * 构造函数
     *
     * @param string $pk
     * @param mixed $pkValue
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
