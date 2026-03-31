<?php

namespace FLEA\Exception;

/**
 * 键名不存在异常
 *
 * 当访问不存在的键名时抛出此异常。
 * 实现 PSR-11 NotFoundExceptionInterface 接口。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 * @see     \Psr\Container\NotFoundExceptionInterface
 */
class NotExistsKeyName extends \FLEA\Exception implements \Psr\Container\NotFoundExceptionInterface
{
    /**
     * @var string 不存在的键名
     */
    public $keyname;

    /**
     * 构造函数
     *
     * @param string $keyname 不存在的键名
     */
    public function __construct(string $keyname)
    {
        $this->keyname = $keyname;
        parent::__construct(sprintf(_ET(0x0102009), $keyname));
    }
}
