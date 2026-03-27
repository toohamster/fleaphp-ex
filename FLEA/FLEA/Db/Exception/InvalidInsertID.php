<?php

namespace FLEA\Db\Exception;

/**
 * \FLEA\Db\Exception\InvalidInsertID 异常指示无法获取刚刚插入的记录的主键值
 *
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
