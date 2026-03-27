<?php

namespace FLEA\Exception;

class MissingArguments extends \FLEA\Exception
{
    /**
     * 缺少的参数
     * @var mixed
     */
    public $args;

    /**
     * 构造函数
     * @param mixed $args
     */
    public function __construct($args)
    {
        $this->args = $args;
        if (is_array($args)) {
            $args = implode(', ', $args);
        }
        $code = 0x0102007;
        $msg = sprintf(_ET($code), $args);
        parent::__construct($msg, $code);
    }
}
