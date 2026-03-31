<?php

namespace FLEA;

/**
 * FLEA 框架基础异常类
 *
 * 所有 FLEA 框架自定义异常的基类，继承自 PHP 标准异常类。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class Exception extends \Exception
{
    /**
     * 构造函数
     *
     * @param string $message 异常消息
     * @param int    $code    异常代码
     */
    public function __construct($message = '', $code = 0)
    {
        parent::__construct($message, $code);
    }
}
