<?php

namespace FLEA\Rbac\Exception;

/**
 * InvalidACTFile 异常
 *
 * 指示控制器的 ACT 文件无效。
 * 用于 RBAC 权限控制中 ACT 文件格式错误时的异常抛出。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class InvalidACTFile extends \FLEA\Exception
{
    /**
     * @var string ACT 文件路径
     */
    public $actFilename;

    /**
     * @var string 控制器名称
     */
    public $controllerName;

    /**
     * @var mixed 无效的 ACT 内容
     */
    public $act;

    /**
     * 构造函数
     *
     * @param string      $actFilename    ACT 文件路径
     * @param mixed       $act            ACT 内容
     * @param string|null $controllerName 控制器名
     */
    public function __construct($actFilename, $act, $controllerName = null)
    {
        $this->actFilename = $actFilename;
        $this->act = $act;
        $this->controllerName = $controllerName;

        if ($controllerName) {
            $code = 0x0701002;
            $msg = sprintf(_ET($code), $actFilename, $controllerName);
        } else {
            $code = 0x0701003;
            $msg = sprintf(_ET($code), $actFilename);
        }
        parent::__construct($msg, $code);
    }
}
