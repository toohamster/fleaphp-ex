<?php

namespace FLEA\Db\Exception;

/**
 * \FLEA\Db\Exception\MetaColumnsFailed 异常指示查询数据表的元数据时发生错误
 *
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
