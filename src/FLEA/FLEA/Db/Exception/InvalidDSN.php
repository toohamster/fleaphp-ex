<?php

namespace FLEA\Db\Exception;

/**
 * InvalidDSN 异常
 *
 * 指示没有提供有效的 DSN 设置。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class InvalidDSN extends \FLEA\Exception
{
    /**
     * @var mixed DSN 配置（已移除密码信息）
     */
    public $dsn;

    /**
     * 构造函数
     *
     * @param mixed $dsn DSN 配置
     */
    public function __construct($dsn)
    {
        unset($this->dsn['password']);
        $this->dsn = $dsn;
        $code = 0x06ff001;
        parent::__construct(_ET($code), $code);
    }
}
