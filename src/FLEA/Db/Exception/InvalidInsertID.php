<?php

namespace FLEA\Db\Exception;

/**
 * InvalidInsertID 异常
 *
 * 指示无法获取刚刚插入记录的主键值。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class InvalidInsertID extends \FLEA\Exception
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $code = 0x06ff008;
        parent::__construct(_ET($code), $code);
    }
}
