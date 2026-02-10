<?php


/**
 * 定义 FLEA_Rbac_Exception_InvalidACT 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: InvalidACT.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Rbac_Exception_InvalidACT 异常指示一个无效的 ACT
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class FLEA_Rbac_Exception_InvalidACT extends FLEA_Exception
{
    /**
     * 无效的 ACT 内容
     *
     * @var mixed
     */
    var $act;

    /**
     * 构造函数
     *
     * @param mixed $act
     *
     * @return FLEA_Rbac_Exception_InvalidACT
     */
    function FLEA_Rbac_Exception_InvalidACT($act)
    {
        $this->act = $act;
        $code = 0x0701001;
        parent::FLEA_Exception(_ET($code), $code);
    }
}
