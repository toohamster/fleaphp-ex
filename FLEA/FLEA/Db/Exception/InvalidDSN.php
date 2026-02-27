<?php

namespace FLEA\Db\Exception;


/**
 * 定义 InvalidDSN 异常
 *
 * InvalidDSN 异常指示没有提供有效的 DSN 设置
 *
 * @package Exception
 * @version 1.0
 */
class InvalidDSN extends \FLEA\Exception
{
    public $dsn;

    /**
     * 构造函数
     *
     * @param $dsn
     */
    function __construct($dsn)
    {
        unset($this->dsn['password']);
        $this->dsn = $dsn;
        $code = 0x06ff001;
        parent::__construct(_ET($code), $code);
    }
}
