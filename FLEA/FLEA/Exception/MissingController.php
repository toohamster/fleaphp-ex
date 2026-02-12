<?php


/**
 * 定义 FLEA_Exception_MissingController 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: MissingController.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Exception_MissingController 指示请求的控制器没有找到
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class FLEA_Exception_MissingController extends FLEA_Exception
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
     * @return FLEA_Exception_MissingController
     */
    public function __construct(string $controllerName, string $actionName, $arguments = null, ?string $controllerClass = null, ?string $actionMethod = null, ?string $controllerClassFilename = null)
    {
        $this->controllerName = $controllerName;
        $this->actionName = $actionName;
        $this->arguments = $arguments;
        $this->controllerClass = $controllerClass;
        $this->actionMethod = $actionMethod;
        $this->controllerClassFilename = $controllerClassFilename;
        $code = 0x0103002;
        parent::__construct(sprintf(_ET($code), $controllerName));
    }
}
