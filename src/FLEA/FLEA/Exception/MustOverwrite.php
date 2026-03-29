<?php

namespace FLEA\Exception;

class MustOverwrite extends \FLEA\Exception
{
    public $prototypeMethod;

    /**
     * 构造函数
     * @param string $prototypeMethod
     */
    public function __construct($prototypeMethod)
    {
        $this->prototypeMethod = $prototypeMethod;
        $code = 0x0102008;
        $msg = sprintf(_ET($code), $prototypeMethod);
        parent::__construct($msg, $code);
    }
}
