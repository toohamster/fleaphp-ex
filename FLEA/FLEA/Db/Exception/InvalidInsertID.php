<?php

namespace FLEA\Db\Exception;


/**
 * 定义 \FLEA\Db\Exception\InvalidInsertID 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: InvalidInsertID.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * \FLEA\Db\Exception\InvalidInsertID 异常指示无法获取刚刚插入的记录的主键值
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class InvalidInsertID extends \FLEA\Exception
{
    /**
     * 构造函数
     */
    function __construct()
    {
        $code = 0x06ff008;
        parent::__construct(_ET($code), $code);
    }
}
