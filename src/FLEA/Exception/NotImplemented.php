<?php

namespace FLEA\Exception;

/**
 * 方法未实现异常
 *
 * 当调用的方法尚未实现时抛出此异常。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class NotImplemented extends \FLEA\Exception
{
    /**
     * @var string 类名称
     */
    public $className;

    /**
     * @var string 方法名称
     */
    public $methodName;

    /**
     * 构造函数
     *
     * @param string $method 方法名
     * @param string $class  类名（可选）
     */
    public function __construct(string $method, string $class = '')
    {
        $this->className = $class;
        $this->methodName = $method;
        if ($class) {
            $code = 0x010200a;
            parent::__construct(sprintf(_ET($code), $class, $method));
        } else {
            $code = 0x010200b;
            parent::__construct(sprintf(_ET($code), $method));
        }
    }
}
