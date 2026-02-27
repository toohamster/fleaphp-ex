<?php

namespace FLEA\Exception;

/**
 * 定义 NotExistsKeyName 异常
 *
 * NotExistsKeyName 异常指示需要的键名不存在
 *
 * @package Exception
 * @version 1.0
 */
class NotExistsKeyName extends \FLEA\Exception
{
    public $keyname;

    /**
     * 构造函数
     *
     * @param string $keyname
     */
    public function __construct(string $keyname)
    {
        $this->keyname = $keyname;
        parent::__construct(sprintf(_ET(0x0102009), $keyname));
    }
}
