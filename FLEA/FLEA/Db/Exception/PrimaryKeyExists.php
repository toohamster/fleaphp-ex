<?php


/**
 * 定义 FLEA_Db_Exception_PrimaryKeyExists 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: PrimaryKeyExists.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Db_Exception_PrimaryKeyExists 异常指示在不需要主键值的时候却提供了主键值
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class FLEA_Db_Exception_PrimaryKeyExists extends FLEA_Exception
{
    /**
     * 主键字段名
     *
     * @var string
     */
    public $primaryKey;

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
     *
     * @return FLEA_Db_Exception_PrimaryKeyExists
     */
    function __construct($pk, $pkValue = null)
    {
        $this->primaryKey = $pk;
        $this->pkValue = $pkValue;
        $code = 0x06ff004;
        $msg = sprintf(_ET($code), $pk, $pkValue);
        parent::__construct($msg, $code);
    }
}
