<?php

namespace FLEA\Exception;

/**
 * 无效参数异常
 *
 * 当传入无效的参数值时抛出此异常。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class InvalidArguments extends \FLEA\Exception
{
    /**
     * @var string 参数名
     */
    public $arg;

    /**
     * @var mixed 参数值
     */
    public $value;

    /**
     * 构造函数
     *
     * @param string $arg   参数名
     * @param mixed  $value 参数值
     */
    public function __construct(string $arg, $value = null)
    {
        $this->arg = $arg;
        $this->value = $value;
        parent::__construct(sprintf(_ET(0x0102006), $arg));
    }
}
