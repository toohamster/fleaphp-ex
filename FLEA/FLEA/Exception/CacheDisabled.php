<?php


/**
 * 定义 FLEA_Exception_CacheDisabled 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: CacheDisabled.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Exception_CacheDisabled 异常指示缓存功能被禁用
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class FLEA_Exception_CacheDisabled extends FLEA_Exception
{
    /**
     * 缓存目录
     */
    public $cacheDir;

    /**
     * 构造函数
     *
     * @param string $cacheDir
     *
     * @return FLEA_Exception_CacheDisabled
     */
    public function __construct($cacheDir)
    {
        $this->cacheDir = $cacheDir;
        parent::__construct(_ET(0x010200d));
    }
}