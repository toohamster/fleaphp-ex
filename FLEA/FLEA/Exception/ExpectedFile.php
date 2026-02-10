<?php


/**
 * 定义 FLEA_Exception_ExpectedFile 异常
 *
 * @author toohamster
 * @package Exception
 * @version $Id: ExpectedFile.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Exception_ExpectedFile 异常指示需要的文件没有找到
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class FLEA_Exception_ExpectedFile extends FLEA_Exception
{
    public $filename;

    /**
     * 构造函数
     *
     * @param string $filename
     *
     * @return FLEA_Exception_ExpectedFile
     */
    function FLEA_Exception_ExpectedFile($filename)
    {
        $this->filename = $filename;
        $code = 0x0102001;
        $msg = sprintf(_ET($code), $filename);
        parent::FLEA_Exception($msg, $code);
    }
}
