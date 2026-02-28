<?php

namespace FLEA\Db\Exception;


/**
 * 定义 \FLEA\Db\Exception\MetaColumnsFailed 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: MetaColumnsFailed.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * \FLEA\Db\Exception\MetaColumnsFailed 异常指示查询数据表的元数据时发生错误
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class MetaColumnsFailed extends \FLEA\Exception
{
    public string $tableName;

    /**
     * 构造函数
     *
     * @param string $tableName
     */
    public function __construct($tableName)
    {
        $code = 0x06ff007;
        parent::__construct(sprintf(_ET($code), $tableName), $code);
    }
}
