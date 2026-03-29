<?php

namespace FLEA\Exception;

class InvalidArguments extends \FLEA\Exception
{
    public $arg;
    public $value;

    /**
     * 构造函数
     * @param string $arg
     * @param mixed $value
     */
    public function __construct(string $arg, $value = null)
    {
        $this->arg = $arg;
        $this->value = $value;
        parent::__construct(sprintf(_ET(0x0102006), $arg));
    }
}
