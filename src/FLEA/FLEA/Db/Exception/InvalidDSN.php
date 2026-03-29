<?php

namespace FLEA\Db\Exception;

/**
 * InvalidDSN 异常指示没有提供有效的 DSN 设置
 */
class InvalidDSN extends \FLEA\Exception
{
    public $dsn;

    /**
     * 构造函数
     *
     * @param mixed $dsn
     */
    public function __construct($dsn)
    {
        unset($this->dsn['password']);
        $this->dsn = $dsn;
        $code = 0x06ff001;
        parent::__construct(_ET($code), $code);
    }
}
