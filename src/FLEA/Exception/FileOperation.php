<?php

namespace FLEA\Exception;

/**
 * 文件操作异常
 *
 * 当文件操作失败时抛出此异常。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class FileOperation extends \FLEA\Exception
{
    /**
     * @var string 正在进行的文件操作
     */
    public $operation;

    /**
     * @var array 操作的参数
     */
    public $args;

    /**
     * 构造函数
     *
     * @param string $operation 文件操作名称
     * @param mixed  ...$args  操作参数
     */
    public function __construct(string $opeation)
    {
        $this->operation = $opeation;
        $args = func_get_args();
        array_shift($args);
        $this->args = $args;
        $func = $opeation . '(' . implode(', ', $args) . ')';
        parent::__construct(sprintf(_ET(0x0102005), $func));
    }
}
