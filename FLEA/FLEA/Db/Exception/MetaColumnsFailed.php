<?php


/**
 * 定义 FLEA_Db_Exception_MetaColumnsFailed 异常
 *
 * @copyright Copyright (c) 2005 - 2008 QeeYuan China Inc. (http://www.qeeyuan.com)
 * @author 起源科技 (www.qeeyuan.com)
 * @package Exception
 * @version $Id: MetaColumnsFailed.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Db_Exception_MetaColumnsFailed 异常指示查询数据表的元数据时发生错误
 *
 * @package Exception
 * @author 起源科技 (www.qeeyuan.com)
 * @version 1.0
 */
class FLEA_Db_Exception_MetaColumnsFailed extends FLEA_Exception
{
    var $tableName;

    /**
     * 构造函数
     *
     * @param string $tableName
     *
     * @return FLEA_Db_Exception_MetaColumnsFailed
     */
    function FLEA_Db_Exception_MetaColumnsFailed($tableName)
    {
        $code = 0x06ff007;
        parent::FLEA_Exception(sprintf(_ET($code), $tableName), $code);
    }
}
