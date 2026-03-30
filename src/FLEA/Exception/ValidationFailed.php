<?php

namespace FLEA\Exception;

/**
 * 验证失败异常
 *
 * 当数据验证失败时抛出此异常。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class ValidationFailed extends \FLEA\Exception
{
    /**
     * @var mixed 被验证的数据
     */
    public $data;

    /**
     * @var array 验证结果（错误信息数组）
     */
    public $result;

    /**
     * 构造函数
     *
     * @param array $result 验证结果（错误信息数组）
     * @param mixed $data   被验证的数据
     */
    public function __construct(array $result, $data = null)
    {
        $this->result = $result;
        $this->data = $data;
        $code = 0x0407001;
        $msg = sprintf(_ET($code), implode(', ', array_keys((array)$result)));
        parent::__construct($msg, $code);
    }
}
