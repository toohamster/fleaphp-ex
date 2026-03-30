<?php

namespace FLEA\Db\Exception;

/**
 * MissingDSN 异常
 *
 * 指示没有提供连接数据库需要的 dbDSN 应用程序设置。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class MissingDSN extends \FLEA\Exception
{
    /**
     * 构造函数
     */
    public function __construct()
    {
        $code = 0x06ff002;
        parent::__construct(_ET($code), $code);
    }
}
