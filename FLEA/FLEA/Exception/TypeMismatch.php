<?php

namespace FLEA\Exception;

/**
 * 定义 TypeMismatch 异常
 *
 * TypeMismatch 异常指示一个类型不匹配错误
 *
 * @package Exception
 * @version 1.0
 */
class TypeMismatch extends \FLEA\Exception
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
     */
    public function __construct(string $arg, string $expected, string $actual)
    {
        $this->arg = $arg;
        $this->expected = $expected;
        $this->actual = $actual;
        $code = 0x010200c;
        $msg = sprintf(_ET($code), $arg, $expected, $actual);
        parent::__construct($msg, $code);
    }
}
