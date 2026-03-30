<?php

namespace FLEA\Exception;

/**
 * 预期的文件不存在异常
 *
 * 当指定的文件不存在时抛出此异常。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class ExpectedFile extends \FLEA\Exception
{
    /**
     * @var string 预期的文件路径
     */
    public $filename;

    /**
     * 构造函数
     *
     * @param string $filename 预期的文件路径
     */
    public function __construct(string $filename)
    {
        $this->filename = $filename;
        $code = 0x0102001;
        $msg = sprintf(_ET($code), $filename);
        parent::__construct($msg, $code);
    }
}
