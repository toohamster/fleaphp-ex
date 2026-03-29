<?php

namespace FLEA\Rbac\Exception;

/**
 * \FLEA\Rbac\Exception\InvalidACT 异常指示一个无效的 ACT
 *
 */
class InvalidACT extends \FLEA\Exception
{
    /**
     * 无效的 ACT 内容
     *
     * @var mixed
     */
    public $act;

    /**
     * 构造函数
     *
     * @param mixed $act
     */
    public function __construct($act)
    {
        $this->act = $act;
        $code = 0x0701001;
        parent::__construct(_ET($code), $code);
    }
}
