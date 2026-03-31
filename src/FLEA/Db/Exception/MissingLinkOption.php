<?php

namespace FLEA\Db\Exception;

/**
 * MissingLinkOption 异常
 *
 * 指示创建 TableLink 对象时没有提供必须的选项。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class MissingLinkOption extends \FLEA\Exception
{
    /**
     * @var string 缺少的选项名
     */
    public string $option;

    /**
     * 构造函数
     *
     * @param string $option 缺少的选项名
     */
    public function __construct($option)
    {
        $this->option = $option;
        $code = 0x0202002;
        parent::__construct(sprintf(_ET($code), $option));
    }
}
