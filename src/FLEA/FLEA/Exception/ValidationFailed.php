<?php

namespace FLEA\Exception;

class ValidationFailed extends \FLEA\Exception
{
    /**
     * 被验证的数据
     * @var mixed
     */
    public $data;

    /**
     * 验证结果
     * @var array
     */
    public $result;

    /**
     * 构造函数
     * @param array $result
     * @param mixed $data
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
