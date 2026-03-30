<?php

namespace FLEA\Exception;

/**
 * 缓存禁用异常
 *
 * 当缓存目录不可用但尝试使用缓存时抛出此异常。
 *
 * @package FLEA
 * @author  toohamster
 * @version 2.0.0
 */
class CacheDisabled extends \FLEA\Exception
{
    /**
     * @var string 缓存目录路径
     */
    public $cacheDir;

    /**
     * 构造函数
     *
     * @param string $cacheDir 缓存目录路径
     */
    public function __construct(string $cacheDir)
    {
        $this->cacheDir = $cacheDir;
        parent::__construct(_ET(0x010200d));
    }
}
