<?php


/**
 * 定义 FLEA_Exception_ExpectedFile 异常
 *
 * @copyright Copyright (c) 2005 - 2008 QeeYuan China Inc. (http://www.qeeyuan.com)
 * @author 起源科技 (www.qeeyuan.com)
 * @package Exception
 * @version $Id: ExpectedFile.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Exception_ExpectedFile 异常指示需要的文件没有找到
 *
 * @package Exception
 * @author 起源科技 (www.qeeyuan.com)
 * @version 1.0
 */
class FLEA_Exception_ExpectedFile extends FLEA_Exception
{
    var $filename;

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
