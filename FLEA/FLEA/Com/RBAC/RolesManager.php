<?php


/**
 * 定义 FLEA_Com_RBAC_RolesManager 类，该类仅仅是 FLEA_Rbac_RolesManager 的别名
 *
 * @author toohamster
 * @package Deprecated
 * @version $Id: RolesManager.php 972 2007-10-09 20:56:54Z qeeyuan $
 */

// {{{ includes
FLEA::loadClass('FLEA_Rbac_RolesManager');
// }}}

/**
 * 开发者应该直接使用 FLEA_Rbac_RolesManager 类
 *
 * @deprecated
 * @package Core
 */
class FLEA_Com_RBAC_RolesManager extends FLEA_Rbac_RolesManager
{
}
