<?php


/**
 * 定义 FLEA_Db_Exception_MissingPrimaryKey 异常
 *
 * @copyright Copyright (c) 2005 - 2008 QeeYuan China Inc. (http://www.qeeyuan.com)
 * @author 起源科技 (www.qeeyuan.com)
 * @package Exception
 * @version $Id: MissingPrimaryKey.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Db_Exception_MissingPrimaryKey 异常指示没有提供主键字段值
 *
 * @package Exception
 * @author 起源科技 (www.qeeyuan.com)
 * @version 1.0
 */
class FLEA_Db_Exception_MissingPrimaryKey extends FLEA_Exception
{
    /**
     * 主键字段名
     *
     * @var string
     */
    var $primaryKey;

    /**
     * 构造函数
     *
     * @param string $pk
     *
     * @return FLEA_Db_Exception_MissingPrimaryKey
     */
    function FLEA_Db_Exception_MissingPrimaryKey($pk)
    {
        $this->primaryKey = $pk;
        $code = 0x06ff003;
        parent::FLEA_Exception(sprintf(_ET($code), $pk));
    }
}
