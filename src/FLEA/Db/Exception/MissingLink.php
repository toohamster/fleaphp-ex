<?php

namespace FLEA\Db\Exception;

/**
 * MissingLink 异常
 *
 * 指示尝试访问的关联不存在。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class MissingLink extends \FLEA\Exception
{
    /**
     * @var string 不存在的关联名称
     */
    public string $name;

    /**
     * 构造函数
     *
     * @param string $name 关联名称
     */
    public function __construct($name)
    {
        $this->name = $name;
        $code = 0x06ff009;
        parent::__construct(sprintf(_ET($code), $name), $code);
    }
}
