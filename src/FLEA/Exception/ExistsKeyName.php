<?php

namespace FLEA\Exception;

/**
 * 键名已存在异常
 *
 * 当尝试注册已存在的键名时抛出此异常。
 * 实现 PSR-11 ContainerExceptionInterface 接口。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 * @see     \Psr\Container\ContainerExceptionInterface
 */
class ExistsKeyName extends \FLEA\Exception implements \Psr\Container\ContainerExceptionInterface
{
    /**
     * @var string 已存在的键名
     */
    public $keyname;

    /**
     * 构造函数
     *
     * @param string $keyname 已存在的键名
     */
    public function __construct(string $keyname)
    {
        $this->keyname = $keyname;
        parent::__construct(sprintf(_ET(0x0102004), $keyname));
    }
}
