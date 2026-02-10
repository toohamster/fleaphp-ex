<?php


/**
 * 定义 FLEA_View_Exception_NotConfigurationSmarty 类
 *
 * @copyright Copyright (c) 2005 - 2008 QeeYuan China Inc. (http://www.qeeyuan.com)
 * @author 起源科技 (www.qeeyuan.com)
 * @package Exception
 * @version $Id: NotConfigurationSmarty.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_View_Exception_NotConfigurationSmartySmarty 表示开发者
 * 没有为 FLEA_View_Smarty 提供初始化 Smarty 模版引擎需要的设置
 *
 * @package Exception
 * @author 起源科技 (www.qeeyuan.com)
 * @version 1.0
 */
class FLEA_View_Exception_NotConfigurationSmarty extends FLEA_Exception
{
    function FLEA_View_Exception_NotConfigurationSmarty()
    {
        $code = 0x0902001;
        parent::FLEA_Exception(_ET($code), $code);
    }
}
