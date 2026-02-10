<?php


/**
 * 定义 FLEA_Com_RBAC_Exception_InvalidACTFile 异常，是 FLEA_Rbac_Exception_InvalidACTFile 的别名
 *
 * @author toohamster
 * @package Exception
 * @version $Id: InvalidACTFile.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

FLEA::loadClass('FLEA_Rbac_Exception_InvalidACTFile');

/**
 * 开发者应该使用 FLEA_Rbac_Exception_InvalidACTFile
 *
 * @package Exception
 * @author toohamster
 * @version 1.0
 */
class FLEA_Com_RBAC_Exception_InvalidACTFile extends FLEA_Rbac_Exception_InvalidACTFile
{
}
