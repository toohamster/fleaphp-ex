<?php

namespace FLEA\Exception;

/**
 * 定义 ExpectedFile 异常
 *
 * ExpectedFile 异常指示需要的文件没有找到
 *
 * @package Exception
 * @version 1.0
 */
class ExpectedFile extends \FLEA\Exception
{
    public $filename;

    /**
     * 构造函数
     *
     * @param string $filename
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
        $code = 0x0102001;
        $msg = sprintf(_ET($code), $filename);
        parent::__construct($msg, $code);
    }
}
