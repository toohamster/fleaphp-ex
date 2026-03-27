<?php

namespace FLEA\Exception;

class ExistsKeyName extends \FLEA\Exception implements \Psr\Container\ContainerExceptionInterface
{
    public $keyname;

    /**
     * 构造函数
     * @param string $keyname
     */
    public function __construct(string $keyname)
    {
        $this->keyname = $keyname;
        parent::__construct(sprintf(_ET(0x0102004), $keyname));
    }
}
