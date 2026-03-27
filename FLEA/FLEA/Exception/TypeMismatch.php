<?php

namespace FLEA\Exception;

class TypeMismatch extends \FLEA\Exception
{
    public $arg;
    public $expected;
    public $actual;

    /**
     * 构造函数
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
