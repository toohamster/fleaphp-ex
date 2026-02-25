<?php



namespace FLEA\Exception;
/**
 * 定义 \FLEA\Exception_MustOverwrite 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: MustOverwrite.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * \FLEA\Exception_MustOverwrite 异常指示某个方法必须在派生类中重写
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class MustOverwrite extends \FLEA\Exception
{
    public $prototypeMethod;

    /**
     * 构造函数
     *
     * @param string $prototypeMethod
     *
     * @return \FLEA\Exception_MustOverwrite
     */
    public function __construct($prototypeMethod)
    {
        $this->prototypeMethod = $prototypeMethod;
        $code = 0x0102008;
        $msg = sprintf(_ET($code), $prototypeMethod);
        parent::__construct($msg, $code);
    }
}
