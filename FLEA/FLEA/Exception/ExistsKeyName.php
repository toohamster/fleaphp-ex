<?php


/**
 * 定义 FLEA_Exception_ExistsKeyName 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: ExistsKeyName.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Exception_ExistsKeyName 异常指示需要的键名已经存在
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class FLEA_Exception_ExistsKeyName extends FLEA_Exception
{
    public $keyname;

    /**
     * 构造函数
     *
     * @param string $keyname
     *
     * @return FLEA_Exception_ExistsKeyName
     */
    public function __construct($keyname)
    {
        $this->keyname = $keyname;
        parent::__construct(sprintf(_ET(0x0102004), $keyname));
    }
}