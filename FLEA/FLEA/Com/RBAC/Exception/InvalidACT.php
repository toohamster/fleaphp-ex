<?php


/**
 * 定义 FLEA_Com_RBAC_Exception_InvalidACT 异常，是 FLEA_Rbac_Exception_InvalidACT 的别名
 *
 * @author toohamster
 * @package Exception
 * @version $Id: InvalidACT.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

FLEA::loadClass('FLEA_Rbac_Exception_InvalidACT');

/**
 * 开发者应该直接使用 FLEA_Rbac_Exception_InvalidACT 类
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class FLEA_Com_RBAC_Exception_InvalidACT extends FLEA_Rbac_Exception_InvalidACT
{
}
