<?php

namespace FLEA\Db\Exception;

/**
 * \FLEA\Db\Exception\MissingLink 异常指示尝试访问的关联不存在
 *
 */
class MissingLink extends \FLEA\Exception
{
    public string $name;

    /**
     * 构造函数
     *
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $code = 0x06ff009;
        parent::__construct(sprintf(_ET($code), $name), $code);
    }
}
