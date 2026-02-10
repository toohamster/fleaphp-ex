<?php


/**
 * 定义 FLEA_Db_Exception_MissingLinkOption 异常
 *
 * @copyright Copyright (c) 2005 - 2008 QeeYuan China Inc. (http://www.qeeyuan.com)
 * @author 起源科技 (www.qeeyuan.com)
 * @package Exception
 * @version $Id: MissingLinkOption.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Db_Exception_MissingLinkOption 异常指示创建 TableLink 对象时没有提供必须的选项
 *
 * @package Exception
 * @author 起源科技 (www.qeeyuan.com)
 * @version 1.0
 */
class FLEA_Db_Exception_MissingLinkOption extends FLEA_Exception
{
    /**
     * 缺少的选项名
     *
     * @var string
     */
    var $option;

    /**
     * 构造函数
     *
     * @param string $option
     *
     * @return FLEA_Db_Exception_MissingLinkOption
     */
    function FLEA_Db_Exception_MissingLinkOption($option)
    {
        $this->option = $option;
        $code = 0x0202002;
        parent::FLEA_Exception(sprintf(_ET($code), $option));
    }
}
