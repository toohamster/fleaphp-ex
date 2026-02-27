<?php



namespace FLEA\Exception;
/**
 * 定义 \FLEA\Exception_InvalidArguments 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: InvalidArguments.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * \FLEA\Exception_InvalidArguments 异常指示一个参数错误
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class InvalidArguments extends \FLEA\Exception
{
    public $arg;
    public $value;

    /**
     * 构造函数
     *
     * @param string $arg
     * @param mixed $value
     */
    public function __construct(string $arg, $value = null)
    {
        $this->arg = $arg;
        $this->value = $value;
        parent::__construct(sprintf(_ET(0x0102006), $arg));
    }
}
