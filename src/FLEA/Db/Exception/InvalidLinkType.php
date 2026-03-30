<?php

namespace FLEA\Db\Exception;

/**
 * InvalidLinkType 异常
 *
 * 指示无效的数据表关联类型。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class InvalidLinkType extends \FLEA\Exception
{
    /**
     * @var mixed 无效的关联类型
     */
    public $type;

    /**
     * 构造函数
     *
     * @param mixed $type 无效的关联类型
     */
    public function __construct($type)
    {
        $this->type = $type;
        $code = 0x0202001;
        parent::__construct(sprintf(_ET($code), $type), $code);
    }
}
