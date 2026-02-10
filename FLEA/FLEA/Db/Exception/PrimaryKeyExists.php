<?php


/**
 * 定义 FLEA_Db_Exception_PrimaryKeyExists 异常
 *
 * @copyright Copyright (c) 2005 - 2008 QeeYuan China Inc. (http://www.qeeyuan.com)
 * @author 起源科技 (www.qeeyuan.com)
 * @package Exception
 * @version $Id: PrimaryKeyExists.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Db_Exception_PrimaryKeyExists 异常指示在不需要主键值的时候却提供了主键值
 *
 * @package Exception
 * @author 起源科技 (www.qeeyuan.com)
 * @version 1.0
 */
class FLEA_Db_Exception_PrimaryKeyExists extends FLEA_Exception
{
    /**
     * 主键字段名
     *
     * @var string
     */
    var $primaryKey;

    /**
     * 主键字段值
     *
     * @var mixed
     */
    var $pkValue;

    /**
     * 构造函数
     *
     * @param string $pk
     * @param mixed $pkValue
     *
     * @return FLEA_Db_Exception_PrimaryKeyExists
     */
    function FLEA_Db_Exception_PrimaryKeyExists($pk, $pkValue = null)
    {
        $this->primaryKey = $pk;
        $this->pkValue = $pkValue;
        $code = 0x06ff004;
        $msg = sprintf(_ET($code), $pk, $pkValue);
        parent::FLEA_Exception($msg, $code);
    }
}
