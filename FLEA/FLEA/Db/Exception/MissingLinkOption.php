<?php


/**
 * 定义 FLEA_Db_Exception_MissingLinkOption 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: MissingLinkOption.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Db_Exception_MissingLinkOption 异常指示创建 TableLink 对象时没有提供必须的选项
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class FLEA_Db_Exception_MissingLinkOption extends FLEA_Exception
{
    /**
     * 缺少的选项名
     *
     * @var string
     */
    public $option;

    /**
     * 构造函数
     *
     * @param string $option
     *
     * @return FLEA_Db_Exception_MissingLinkOption
     */
    function __construct($option)
    {
        $this->option = $option;
        $code = 0x0202002;
        parent::__construct(sprintf(_ET($code), $option));
    }
}
