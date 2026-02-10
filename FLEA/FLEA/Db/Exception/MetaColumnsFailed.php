<?php


/**
 * 定义 FLEA_Db_Exception_MetaColumnsFailed 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: MetaColumnsFailed.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Db_Exception_MetaColumnsFailed 异常指示查询数据表的元数据时发生错误
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class FLEA_Db_Exception_MetaColumnsFailed extends FLEA_Exception
{
    public $tableName;

    /**
     * 构造函数
     *
     * @param string $tableName
     *
     * @return FLEA_Db_Exception_MetaColumnsFailed
     */
    function __construct($tableName)
    {
        $code = 0x06ff007;
        parent::__construct(sprintf(_ET($code), $tableName), $code);
    }
}
