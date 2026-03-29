<?php

namespace FLEA\Exception;

class CacheDisabled extends \FLEA\Exception
{
    /**
     * 缓存目录
     */
    public $cacheDir;

    /**
     * 构造函数
     * @param string $cacheDir
     */
    public function __construct(string $cacheDir)
    {
        $this->cacheDir = $cacheDir;
        parent::__construct(_ET(0x010200d));
    }
}
