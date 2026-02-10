<?php


/**
 * 定义 FLEA_Db_Exception_InvalidInsertID 异常
 *
 * @copyright Copyright (c) 2005 - 2008 QeeYuan China Inc. (http://www.qeeyuan.com)
 * @author 起源科技 (www.qeeyuan.com)
 * @package Exception
 * @version $Id: InvalidInsertID.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Db_Exception_InvalidInsertID 异常指示无法获取刚刚插入的记录的主键值
 *
 * @package Exception
 * @author 起源科技 (www.qeeyuan.com)
 * @version 1.0
 */
class FLEA_Db_Exception_InvalidInsertID extends FLEA_Exception
{
    /**
     * 构造函数
     *
     * @return FLEA_Db_Exception_InvalidInsertID
     */
    function FLEA_Db_Exception_InvalidInsertID()
    {
        $code = 0x06ff008;
        parent::FLEA_Exception(_ET($code), $code);
    }
}
