<?php


/**
 * 定义 FLEA_Db_Exception_InvalidLinkType 异常
 *
 * @copyright Copyright (c) 2005 - 2008 QeeYuan China Inc. (http://www.qeeyuan.com)
 * @author 起源科技 (www.qeeyuan.com)
 * @package Exception
 * @version $Id: InvalidLinkType.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Db_Exception_InvalidLinkType 异常指示无效的数据表关联类型
 *
 * @package Exception
 * @author 起源科技 (www.qeeyuan.com)
 * @version 1.0
 */
class FLEA_Db_Exception_InvalidLinkType extends FLEA_Exception
{
    var $type;

    /**
     * 构造函数
     *
     * @param $type
     *
     * @return FLEA_Db_Exception_InvalidDSN
     */
    function FLEA_Db_Exception_InvalidLinkType($type)
    {
        $this->type = $type;
        $code = 0x0202001;
        parent::FLEA_Exception(sprintf(_ET($code), $type), $code);
    }
}
