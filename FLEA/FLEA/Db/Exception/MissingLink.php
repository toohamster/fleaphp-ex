<?php

namespace FLEA\Db\Exception;


/**
 * 定义 \FLEA\Db\Exception\MissingLink 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: MissingLink.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * \FLEA\Db\Exception\MissingLink 异常指示尝试访问的关联不存在
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class MissingLink extends \FLEA\Exception
{
    public $name;

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
