<?php

namespace FLEA\Dispatcher\Exception;

/**
 * \FLEA\Dispatcher\Exception\CheckFailed 异常指示用户试图访问的控制器方法不允许该用户访问
 *
 */
class CheckFailed extends \FLEA\Exception
{
    public $controllerName;
    public $actionName;
    public $roles;
    public $act;

    /**
     * 构造函数
     *
     * @param string $controllerName
     * @param string $actionName
     * @param array $act
     * @param array $roles
     */
    public function __construct($controllerName, $actionName,
            $act = null, $roles = null)
    {
        $this->controllerName = $controllerName;
        $this->actionName = $actionName;
        $this->act = $act;
        $this->roles = $roles;
        $code = 0x0701004;
        $msg = sprintf(_ET($code), $controllerName, $actionName);
        parent::__construct($msg, $code);
    }
}
