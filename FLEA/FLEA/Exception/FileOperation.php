<?php



namespace FLEA\Exception;
/**
 * 定义 \FLEA\Exception_FileOperation 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: FileOperation.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * \FLEA\Exception_FileOperation 异常指示文件系统操作失败
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class FileOperation extends \FLEA\Exception
{
    /**
     * 正在进行的文件操作
     *
     * @var string
     */
    public $operation;

    /**
     * 操作的参数
     *
     * @var array
     */
    public $args;

    /**
     * 构造函数
     *
     * @param string $opeation
     */
    public function __construct(string $opeation)
    {
        $this->operation = $opeation;
        $args = func_get_args();
        array_shift($args);
        $this->args = $args;
        $func = $opeation . '(' . implode(', ', $args) . ')';
        parent::__construct(sprintf(_ET(0x0102005), $func));
    }
}
