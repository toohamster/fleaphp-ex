<?php

namespace FLEA\Exception;

/**
 * 缺少参数异常
 *
 * 当缺少必需的参数时抛出此异常。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class MissingArguments extends \FLEA\Exception
{
    /**
     * @var mixed 缺少的参数
     */
    public $args;

    /**
     * 构造函数
     *
     * @param mixed $args 缺少的参数（可以是字符串或数组）
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
