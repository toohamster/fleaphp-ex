<?php


/**
 * 定义 FLEA_Exception_InvalidArguments 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: InvalidArguments.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Exception_InvalidArguments 异常指示一个参数错误
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class FLEA_Exception_InvalidArguments extends FLEA_Exception
{
    public $arg;
    public $value;

    /**
     * 构造函数
     *
     * @param string $arg
     * @param mixed $value
     *
     * @return FLEA_Exception_InvalidArguments
     */
    function FLEA_Exception_InvalidArguments($arg, $value = null)
    {
        $this->arg = $arg;
        $this->value = $value;
        parent::FLEA_Exception(sprintf(_ET(0x0102006), $arg));
    }
}
