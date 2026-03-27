<?php

namespace FLEA\Db\Exception;

/**
 * \FLEA\Db\Exception\InvalidLinkType 异常指示无效的数据表关联类型
 *
 */
class InvalidLinkType extends \FLEA\Exception
{
    public $type;

    /**
     * 构造函数
     *
     * @param $type
     */
    public function __construct($type)
    {
        $this->type = $type;
        $code = 0x0202001;
        parent::__construct(sprintf(_ET($code), $type), $code);
    }
}
