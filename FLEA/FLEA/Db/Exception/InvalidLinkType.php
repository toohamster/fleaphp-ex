<?php

namespace FLEA\Db\Exception;


/**
 * 定义 \FLEA\Db\Exception\InvalidLinkType 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: InvalidLinkType.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * \FLEA\Db\Exception\InvalidLinkType 异常指示无效的数据表关联类型
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class InvalidLinkType extends \FLEA\Exception
{
    public $type;

    /**
     * 构造函数
     *
     * @param $type
     */
    function __construct($type)
    {
        $this->type = $type;
        $code = 0x0202001;
        parent::__construct(sprintf(_ET($code), $type), $code);
    }
}
