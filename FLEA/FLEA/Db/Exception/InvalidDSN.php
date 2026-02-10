<?php


/**
 * 定义 FLEA_Db_Exception_InvalidDSN 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: InvalidDSN.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Db_Exception_InvalidDSN 异常指示没有提供有效的 DSN 设置
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class FLEA_Db_Exception_InvalidDSN extends FLEA_Exception
{
    public $dsn;

    /**
     * 构造函数
     *
     * @param $dsn
     *
     * @return FLEA_Db_Exception_InvalidDSN
     */
    public function __construct($dsn)
    {
        unset($this->dsn['password']);
        $this->dsn = $dsn;
        $code = 0x06ff001;
        parent::__construct(_ET($code), $code);
    }
}