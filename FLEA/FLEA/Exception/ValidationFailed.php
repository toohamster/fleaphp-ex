<?php


/**
 * 定义 FLEA_Exception_ValidationFailed 异常
 *
 * @copyright Copyright (c) 2005 - 2008 QeeYuan China Inc. (http://www.qeeyuan.com)
 * @author 起源科技 (www.qeeyuan.com)
 * @package Exception
 * @version $Id: ValidationFailed.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

/**
 * FLEA_Exception_ValidationFailed 异常指示数据验证失败
 *
 * @package Exception
 * @author 起源科技 (www.qeeyuan.com)
 * @version 1.0
 */
class FLEA_Exception_ValidationFailed extends FLEA_Exception
{
    /**
     * 被验证的数据
     *
     * @var mixed
     */
    var $data;

    /**
     * 验证结果
     *
     * @var array
     */
    var $result;

    /**
     * 构造函数
     *
     * @param array $result
     * @param mixed $data
     *
     * @return FLEA_Exception_ValidationFailed
     */
    function FLEA_Exception_ValidationFailed($result, $data = null)
    {
        $this->result = $result;
        $this->data = $data;
        $code = 0x0407001;
        $msg = sprintf(_ET($code), implode(', ', array_keys((array)$result)));
        parent::FLEA_Exception($msg, $code);
    }
}
