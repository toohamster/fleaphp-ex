<?php


/**
 * 定义 FLEA_Dispatcher_Exception_CheckFailed 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: CheckFailed.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Dispatcher_Exception_CheckFailed 异常指示用户试图访问的控制器方法不允许该用户访问
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class FLEA_Dispatcher_Exception_CheckFailed extends FLEA_Exception
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
     *
     * @return FLEA_Dispatcher_Exception_CheckFailed
     */
    function FLEA_Dispatcher_Exception_CheckFailed($controllerName, $actionName,
            $act = null, $roles = null)
    {
        $this->controllerName = $controllerName;
        $this->actionName = $actionName;
        $this->act = $act;
        $this->roles = $roles;
        $code = 0x0701004;
        $msg = sprintf(_ET($code), $controllerName, $actionName);
        parent::FLEA_Exception($msg, $code);
    }
}
