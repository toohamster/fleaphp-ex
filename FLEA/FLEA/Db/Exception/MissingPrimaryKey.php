<?php

namespace FLEA\Db\Exception;


/**
 * 定义 \FLEA\Db\Exception\MissingPrimaryKey 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: MissingPrimaryKey.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * \FLEA\Db\Exception\MissingPrimaryKey 异常指示没有提供主键字段值
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class MissingPrimaryKey extends \FLEA\Exception
{
    /**
     * 主键字段名
     *
     * @var string
     */
    public $primaryKey;

    /**
     * 构造函数
     *
     * @param string $pk
     *
     * @return \FLEA\Db\Exception\MissingPrimaryKey
     */
    public function __construct($pk)
    {
        $this->primaryKey = $pk;
        $code = 0x06ff003;
        parent::__construct(sprintf(_ET($code), $pk));
    }
}
