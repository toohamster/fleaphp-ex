<?php

namespace FLEA\Exception;

/**
 * 缺少控制器异常
 *
 * 当控制器不存在时抛出此异常。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class MissingController extends \FLEA\Exception
{
    /**
     * @var string 控制器名称
     */
    public $controllerName;

    /**
     * @var string 控制器类名称
     */
    public $controllerClass;

    /**
     * @var string 动作名
     */
    public $actionName;

    /**
     * @var string 动作方法名
     */
    public $actionMethod;

    /**
     * @var mixed 调用参数
     */
    public $arguments;

    /**
     * @var string 控制器的类定义文件
     */
    public $controllerClassFilename;

    /**
     * 构造函数
     *
     * @param string      $controllerName          控制器名
     * @param string      $actionName              动作名
     * @param mixed       $arguments               调用参数
     * @param string|null $controllerClass         控制器类名
     * @param string|null $actionMethod            动作方法名
     * @param string|null $controllerClassFilename 控制器类文件路径
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
