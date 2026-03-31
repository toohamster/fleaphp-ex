<?php

namespace FLEA\Db\Exception;

/**
 * MissingPrimaryKey 异常
 *
 * 指示没有提供主键字段值。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class MissingPrimaryKey extends \FLEA\Exception
{
    /**
     * @var string 主键字段名
     */
    public string $primaryKey;

    /**
     * 构造函数
     *
     * @param string $pk 主键字段名
     */
    public function __construct($pk)
    {
        $this->primaryKey = $pk;
        $code = 0x06ff003;
        parent::__construct(sprintf(_ET($code), $pk));
    }
}
