<?php



namespace FLEA\Exception;
/**
 * 定义 FLEA_Exception_NotImplemented 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: NotImplemented.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Exception_NotImplemented 异常指示某个方法没有实现
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class NotImplemented extends \FLEA\Exception
{
    public $className;
    public $methodName;

    /**
     * 构造函数
     *
     * @param string $method
     * @param string $class
     *
     * @return FLEA_Exception_NotImplemented
     */
    public function __construct(string $method, string $class = '')
    {
        $this->className = $class;
        $this->methodName = $method;
        if ($class) {
            $code = 0x010200a;
            parent::__construct(sprintf(_ET($code), $class, $method));
        } else {
            $code = 0x010200b;
            parent::__construct(sprintf(_ET($code), $method));
        }
    }
}
