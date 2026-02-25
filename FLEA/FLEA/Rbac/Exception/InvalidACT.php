<?php



namespace FLEA\Rbac\Exception;
/**
 * 定义 \FLEA\Rbac\Exception\InvalidACT 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: InvalidACT.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * \FLEA\Rbac\Exception\InvalidACT 异常指示一个无效的 ACT
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
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
     *
     * @return \FLEA\Rbac\Exception\InvalidACT
     */
    function __construct($act)
    {
        $this->act = $act;
        $code = 0x0701001;
        parent::__construct(_ET($code), $code);
    }
}
