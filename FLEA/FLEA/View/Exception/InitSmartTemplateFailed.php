<?php


/**
 * 定义 FLEA_View_Exception_InitSmartTemplateFailed 类
 *
 * @copyright Copyright (c) 2005 - 2008 QeeYuan China Inc. (http://www.qeeyuan.com)
 * @author 起源科技 (www.qeeyuan.com)
 * @package Exception
 * @version $Id: InitSmartTemplateFailed.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_View_Exception_InitSmartTemplateFailed 指示 FLEA_View_SmartTemplate 无法初始化 SmartTemplate 模版引擎
 *
 * @package Exception
 * @author 起源科技 (www.qeeyuan.com)
 * @version 1.0
 */
class FLEA_View_Exception_InitSmartTemplateFailed extends FLEA_Exception
{
    var $filename;

    function FLEA_View_Exception_InitSmartTemplateFailed($filename)
    {
        $this->filename = $filename;
        $code = 0x0903002;
        parent::FLEA_Exception(sprintf(_ET($code), $filename), $code);
    }
}
