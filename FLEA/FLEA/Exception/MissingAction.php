<?php



namespace FLEA\Exception;
/**
 * 定义 \FLEA\Exception_MissingAction 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: MissingAction.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * \FLEA\Exception_MissingAction 指示请求的控制器 Action 方法没有找到
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class MissingAction extends \FLEA\Exception
{
    /**
     * 控制器的名字
     *
     * @var string
     */
    public $controllerName;

    /**
     * 控制器类名称
     *
     * @var string
     */
    public $controllerClass;

    /**
     * 动作名
     *
     * @var string
     */
    public $actionName;

    /**
     * 动作方法名
     *
     * @var string
     */
    public $actionMethod;

    /**
     * 调用参数
     *
     * @var mixed
     */
    public $arguments;

    /**
     * 控制器的类定义文件
     *
     * @var string
     */
    public $controllerClassFilename;

    /**
     * 构造函数
     *
     * @param string $controllerName
     * @param string $actionName
     * @param mixed $arguments
     * @param string $controllerClass
     * @param string $actionMethod
     *
     * @return \FLEA\Exception_MissingAction
     */
    public function __construct(string $controllerName, string $actionName, $arguments = null, ?string $controllerClass = null, ?string $actionMethod = null, ?string $controllerClassFilename = null)
    {
        $this->controllerName = $controllerName;
        $this->actionName = $actionName;
        $this->arguments = $arguments;
        $this->controllerClass = $controllerClass;
        $this->actionMethod = $actionMethod;
        $this->controllerClassFilename = $controllerClassFilename;
        $code = 0x0103001;
        $msg = sprintf(_ET($code), $controllerName, $actionName);
        parent::__construct($msg, $code);
    }
}
