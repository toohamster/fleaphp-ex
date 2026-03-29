<?php

namespace FLEA\Exception;

/**
 * 类型不匹配异常
 *
 * 当参数类型与预期不符时抛出此异常。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class TypeMismatch extends \FLEA\Exception
{
    /**
     * @var string 参数名
     */
    public $arg;

    /**
     * @var string 预期的类型
     */
    public $expected;

    /**
     * @var string 实际的类型
     */
    public $actual;

    /**
     * 构造函数
     *
     * @param string $arg      参数名
     * @param string $expected 预期的类型
     * @param string $actual   实际的类型
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
