<?php

namespace FLEA\Dispatcher\Exception;

/**
 * CheckFailed 异常
 *
 * 指示用户试图访问的控制器方法不允许该用户访问。
 * 用于权限验证失败时的异常抛出。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class CheckFailed extends \FLEA\Exception
{
    /**
     * @var string 控制器名称
     */
    public $controllerName;

    /**
     * @var string 动作名称
     */
    public $actionName;

    /**
     * @var array ACT 配置
     */
    public $roles;

    /**
     * @var array 权限动作
     */
    public $act;

    /**
     * 构造函数
     *
     * @param string      $controllerName 控制器名
     * @param string      $actionName     动作名
     * @param array|null  $act            ACT 配置
     * @param array|null  $roles          用户角色
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
