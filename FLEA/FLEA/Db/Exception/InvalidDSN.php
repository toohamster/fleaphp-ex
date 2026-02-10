<?php


/**
 * 定义 FLEA_Db_Exception_InvalidDSN 异常
 *
 * @copyright Copyright (c) 2005 - 2008 QeeYuan China Inc. (http://www.qeeyuan.com)
 * @author 起源科技 (www.qeeyuan.com)
 * @package Exception
 * @version $Id: InvalidDSN.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Db_Exception_InvalidDSN 异常指示没有提供有效的 DSN 设置
 *
 * @package Exception
 * @author 起源科技 (www.qeeyuan.com)
 * @version 1.0
 */
class FLEA_Db_Exception_InvalidDSN extends FLEA_Exception
{
    var $dsn;

    /**
     * 构造函数
     *
     * @param $dsn
     *
     * @return FLEA_Db_Exception_InvalidDSN
     */
    function FLEA_Db_Exception_InvalidDSN($dsn)
    {
        unset($this->dsn['password']);
        $this->dsn = $dsn;
        $code = 0x06ff001;
        parent::FLEA_Exception(_ET($code), $code);
    }
}
