<?php



namespace FLEA\Exception;
/**
 * 定义 \FLEA\Exception_ExpectedClass 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: ExpectedClass.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * \FLEA\Exception_ExpectedClass 异常指示需要的类没有找到
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class ExpectedClass extends \FLEA\Exception
{
    /**
     * 类名称
     *
     * @var string
     */
    public $className;

    /**
     * 类定义文件
     *
     * @var string
     */
    public $classFile;

    /**
     * 指示文件是否存在
     *
     * @var boolean
     */
    public $fileExists;

    /**
     * 构造函数
     *
     * @param string $className
     * @param string $file
     * @param boolean $fileExists
     */
    public function __construct(string $className, ?string $file = null, bool $fileExists = false)
    {
        $this->className = $className;
        $this->classFile = $file;
        $this->fileExists = $fileExists;
        if ($file) {
            $code = 0x0102002;
            $msg = sprintf(_ET($code), $file, $className);
        } else {
            $code = 0x0102003;
            $msg = sprintf(_ET($code), $className);
        }
        parent::__construct($msg, $code);
    }
}
