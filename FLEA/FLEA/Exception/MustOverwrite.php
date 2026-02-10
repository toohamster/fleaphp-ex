<?php


/**
 * 定义 FLEA_Exception_MustOverwrite 异常
 *
 * @copyright Copyright (c) 2005 - 2008 QeeYuan China Inc. (http://www.qeeyuan.com)
 * @author 起源科技 (www.qeeyuan.com)
 * @package Exception
 * @version $Id: MustOverwrite.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Exception_MustOverwrite 异常指示某个方法必须在派生类中重写
 *
 * @package Exception
 * @author 起源科技 (www.qeeyuan.com)
 * @version 1.0
 */
class FLEA_Exception_MustOverwrite extends FLEA_Exception
{
    var $prototypeMethod;

    /**
     * 构造函数
     *
     * @param string $prototypeMethod
     *
     * @return FLEA_Exception_MustOverwrite
     */
    function FLEA_Exception_MustOverwrite($prototypeMethod)
    {
        $this->prototypeMethod = $prototypeMethod;
        $code = 0x0102008;
        $msg = sprintf(_ET($code), $prototypeMethod);
        parent::FLEA_Exception($msg, $code);
    }
}
