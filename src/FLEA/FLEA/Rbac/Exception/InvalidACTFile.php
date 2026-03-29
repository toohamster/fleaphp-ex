<?php

namespace FLEA\Rbac\Exception;

/**
 * \FLEA\Rbac\Exception\InvalidACTFile 异常指示控制器的 ACT 文件无效
 *
 */
class InvalidACTFile extends \FLEA\Exception
{
    /**
     * ACT 文件名
     *
     * @var string
     */
    public $actFilename;

    /**
     * 控制器名字
     *
     * @var string
     */
    public $controllerName;

    /**
     * 无效的 ACT 内容
     *
     * @var mixed
     */
    public $act;

    /**
     * 构造函数
     *
     * @param string $actFilename
     * @param string $controllerName
     * @param mixed $act
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
