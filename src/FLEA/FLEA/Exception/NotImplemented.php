<?php

namespace FLEA\Exception;

class NotImplemented extends \FLEA\Exception
{
    public $className;
    public $methodName;

    /**
     * 构造函数
     * @param string $method
     * @param string $class
     */
    public function __construct(string $method, string $class = '')
    {
        $this->className = $class;
        $this->methodName = $method;
        if ($class) {
            $code = 0x010200a;
            parent::__construct(sprintf(_ET($code), $class, $method));
        } else {
            $code = 0x010200b;
            parent::__construct(sprintf(_ET($code), $method));
        }
    }
}
