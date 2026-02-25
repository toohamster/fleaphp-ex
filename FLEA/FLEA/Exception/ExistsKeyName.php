<?php

namespace FLEA\Exception;

/**
 * 定义 ExistsKeyName 异常
 *
 * ExistsKeyName 异常指示需要的键名已经存在
 *
 * @package Exception
 * @version 1.0
 */
class ExistsKeyName extends \FLEA\Exception
{
    public $keyname;

    /**
     * 构造函数
     *
     * @param string $keyname
     *
     * @return ExistsKeyName
     */
    public function __construct(string $keyname)
    {
        $this->keyname = $keyname;
        parent::__construct(sprintf(_ET(0x0102004), $keyname));
    }
}
