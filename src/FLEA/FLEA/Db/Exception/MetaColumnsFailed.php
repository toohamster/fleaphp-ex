<?php

namespace FLEA\Db\Exception;

/**
 * MetaColumnsFailed 异常
 *
 * 指示查询数据表的元数据时发生错误。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class MetaColumnsFailed extends \FLEA\Exception
{
    /**
     * @var string 数据表名称
     */
    public string $tableName;

    /**
     * 构造函数
     *
     * @param string $tableName 数据表名
     */
    public function __construct($tableName)
    {
        $code = 0x06ff007;
        parent::__construct(sprintf(_ET($code), $tableName), $code);
    }
}
