<?php

namespace FLEA\Exception;

/**
 * 必须重写方法异常
 *
 * 当子类未重写父类中要求必须重写的方法时抛出此异常。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class MustOverwrite extends \FLEA\Exception
{
    /**
     * @var string 必须重写的原型方法名
     */
    public $prototypeMethod;

    /**
     * 构造函数
     *
     * @param string $prototypeMethod 必须重写的原型方法名
     */
    public function __construct($prototypeMethod)
    {
        $this->prototypeMethod = $prototypeMethod;
        $code = 0x0102008;
        $msg = sprintf(_ET($code), $prototypeMethod);
        parent::__construct($msg, $code);
    }
}
