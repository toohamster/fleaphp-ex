<?php

namespace FLEA\Db\Exception;

/**
 * \FLEA\Db\Exception\MissingPrimaryKey 异常指示没有提供主键字段值
 *
 */
class MissingPrimaryKey extends \FLEA\Exception
{
    /**
     * 主键字段名
     *
     * @var string
     */
    public string $primaryKey;

    /**
     * 构造函数
     *
     * @param string $pk
     */
    public function __construct($pk)
    {
        $this->primaryKey = $pk;
        $code = 0x06ff003;
        parent::__construct(sprintf(_ET($code), $pk));
    }
}
