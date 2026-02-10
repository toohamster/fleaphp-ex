<?php


/**
 * 定义 FLEA_Db_Exception_MissingLink 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: MissingLink.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Db_Exception_MissingLink 异常指示尝试访问的关联不存在
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class FLEA_Db_Exception_MissingLink extends FLEA_Exception
{
    public $name;

    /**
     * 构造函数
     *
     * @param $name
     *
     * @return FLEA_Db_Exception_MissingLink
     */
    function FLEA_Db_Exception_MissingLink($name)
    {
        $this->name = $name;
        $code = 0x06ff009;
        parent::FLEA_Exception(sprintf(_ET($code), $name), $code);
    }
}
