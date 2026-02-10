<?php


/**
 * 定义 FLEA_Exception_TypeMismatch 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: TypeMismatch.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Exception_TypeMismatch 异常指示一个类型不匹配错误
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class FLEA_Exception_TypeMismatch extends FLEA_Exception
{
    public $arg;
    public $expected;
    public $actual;

    /**
     * 构造函数
     *
     * @param string $arg
     * @param string $expected
     * @param string $actual
     *
     * @return FLEA_Exception_TypeMismatch
     */
    function __construct($arg, $expected, $actual)
    {
        $this->arg = $arg;
        $this->expected = $expected;
        $this->actual = $actual;
        $code = 0x010200c;
        $msg = sprintf(_ET($code), $arg, $expected, $actual);
        parent::__construct($msg, $code);
    }
}
