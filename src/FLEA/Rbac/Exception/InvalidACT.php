<?php

namespace FLEA\Rbac\Exception;

/**
 * InvalidACT 异常
 *
 * 指示一个无效的 ACT（Access Control Table）配置。
 * 用于 RBAC 权限控制中 ACT 配置无效时的异常抛出。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class InvalidACT extends \FLEA\Exception
{
    /**
     * @var mixed 无效的 ACT 内容
     */
    public $act;

    /**
     * 构造函数
     *
     * @param mixed $act 无效的 ACT 配置
     */
    public function __construct($act)
    {
        $this->act = $act;
        $code = 0x0701001;
        parent::__construct(_ET($code), $code);
    }
}
