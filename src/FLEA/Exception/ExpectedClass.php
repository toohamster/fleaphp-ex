<?php

namespace FLEA\Exception;

/**
 * 预期的类不存在异常
 *
 * 当指定的类不存在时抛出此异常。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class ExpectedClass extends \FLEA\Exception
{
    /**
     * @var string 预期的类名称
     */
    public $className;

    /**
     * @var string 预期的类定义文件路径
     */
    public $classFile;

    /**
     * @var bool 文件是否存在
     */
    public $fileExists;

    /**
     * 构造函数
     *
     * @param string      $className  预期的类名
     * @param string|null $file       类文件路径（可选）
     * @param bool        $fileExists 文件是否存在
     */
    public function __construct(string $className, ?string $file = null, bool $fileExists = false)
    {
        $this->className = $className;
        $this->classFile = $file;
        $this->fileExists = $fileExists;
        if ($file) {
            $code = 0x0102002;
            $msg = sprintf(_ET($code), $file, $className);
        } else {
            $code = 0x0102003;
            $msg = sprintf(_ET($code), $className);
        }
        parent::__construct($msg, $code);
    }
}
