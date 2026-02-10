<?php


/**
 * 定义 FLEA_Db_Exception_InvalidLinkType 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: InvalidLinkType.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Db_Exception_InvalidLinkType 异常指示无效的数据表关联类型
 *
 * @package Exception
 * @author toohamster
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
