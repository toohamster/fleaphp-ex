<?php

namespace FLEA\Exception;

class ExpectedFile extends \FLEA\Exception
{
    public $filename;

    /**
     * 构造函数
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
