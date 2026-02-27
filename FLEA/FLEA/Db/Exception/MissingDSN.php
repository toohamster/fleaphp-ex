<?php

namespace FLEA\Db\Exception;


/**
 * 定义 \FLEA\Db\Exception\MissingDSN 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: MissingDSN.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * \FLEA\Db\Exception\MissingDSN 异常指示没有提供连接数据库需要的 dbDSN 应用程序设置
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class MissingDSN extends \FLEA\Exception
{
    /**
     * 构造函数
     */
    function __construct()
    {
        $code = 0x06ff002;
        parent::__construct(_ET($code), $code);
    }
}
