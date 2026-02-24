<?php



namespace FLEA\Exception;
/**
 * 定义 FLEA_Exception_MissingArguments 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: MissingArguments.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Exception_MissingArguments 异常指示缺少必须的参数
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class MissingArguments extends \FLEA\Exception
{
    /**
     * 缺少的参数
     *
     * @var mixed
     */
    public $args;

    /**
     * 构造函数
     *
     * @param mixed $args
     *
     * @return FLEA_Exception_MissingArguments
     */
    public function __construct($args)
    {
        $this->args = $args;
        if (is_array($args)) {
            $args = implode(', ', $args);
        }
        $code = 0x0102007;
        $msg = sprintf(_ET($code), $args);
        parent::__construct($msg, $code);
    }
}
